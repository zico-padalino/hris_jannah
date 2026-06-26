const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
const RING_RADIUS = 96;
const RING_CIRCUMFERENCE = 2 * Math.PI * RING_RADIUS;

const POSE_SEQUENCES = {
    attendance: [
        { id: 'center', label: 'Hadapkan wajah lurus ke kamera', arrow: 0, check: (pose) => Math.abs(pose.yaw) < 0.2 },
        { id: 'left', label: 'Putar perlahan wajah ke kiri', arrow: -90, check: (pose) => pose.yaw < -0.25 },
        { id: 'right', label: 'Putar perlahan wajah ke kanan', arrow: 90, check: (pose) => pose.yaw > 0.25 },
        { id: 'center', label: 'Kembali hadap kamera', arrow: 0, check: (pose) => Math.abs(pose.yaw) < 0.2 },
    ],
    enroll: [
        { id: 'center', label: 'Hadapkan wajah lurus ke kamera', arrow: 0, check: (pose) => Math.abs(pose.yaw) < 0.2 },
        { id: 'left', label: 'Putar perlahan wajah ke kiri', arrow: -90, check: (pose) => pose.yaw < -0.25 },
        { id: 'right', label: 'Putar perlahan wajah ke kanan', arrow: 90, check: (pose) => pose.yaw > 0.25 },
        { id: 'up', label: 'Angkat dagu sedikit ke atas', arrow: 180, check: (pose) => pose.pitch < 0.36 },
        { id: 'center', label: 'Kembali hadap kamera', arrow: 0, check: (pose) => Math.abs(pose.yaw) < 0.2 },
    ],
};

async function waitForFaceApi() {
    while (typeof faceapi === 'undefined') {
        await new Promise((resolve) => setTimeout(resolve, 100));
    }
}

function getDetectorOptions() {
    return new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.5 });
}

async function loadModels(statusEl) {
    await waitForFaceApi();
    statusEl.textContent = 'Memuat model AI wajah...';

    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
}

function getCameraErrorMessage() {
    if (!window.isSecureContext) {
        return 'Kamera & GPS memerlukan koneksi aman (HTTPS). '
            + 'Gunakan https://localhost:8443 atau jalankan: npm run serve:https lalu buka https://IP-ANDA:8443';
    }

    if (!navigator.mediaDevices?.getUserMedia) {
        return 'Browser tidak mendukung akses kamera. Gunakan Chrome/Edge terbaru dan izinkan akses kamera.';
    }

    return 'Tidak dapat mengakses kamera. Periksa izin kamera di pengaturan browser.';
}

async function getUserMediaStream(constraints) {
    if (navigator.mediaDevices?.getUserMedia) {
        return navigator.mediaDevices.getUserMedia(constraints);
    }

    const legacyGetUserMedia = navigator.getUserMedia
        || navigator.webkitGetUserMedia
        || navigator.mozGetUserMedia;

    if (!legacyGetUserMedia) {
        throw new Error(getCameraErrorMessage());
    }

    return new Promise((resolve, reject) => {
        legacyGetUserMedia.call(navigator, constraints, resolve, reject);
    });
}

async function startCamera(videoEl, statusEl) {
    if (!window.isSecureContext) {
        throw new Error(getCameraErrorMessage());
    }

    const stream = await getUserMediaStream({
        video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
        audio: false,
    });

    videoEl.srcObject = stream;
    await videoEl.play();
    statusEl.textContent = 'Kamera aktif. Ikuti panduan scan wajah.';
}

function setStatus(statusEl, message, state = 'default') {
    statusEl.textContent = message;
    statusEl.classList.remove('attendance-scan-status--ready', 'attendance-scan-status--error', 'attendance-scan-status--scanning');

    if (state === 'ready') {
        statusEl.classList.add('attendance-scan-status--ready');
    } else if (state === 'error') {
        statusEl.classList.add('attendance-scan-status--error');
    } else if (state === 'scanning') {
        statusEl.classList.add('attendance-scan-status--scanning');
    }
}

function setCameraState(cameraEl, state) {
    if (!cameraEl) {
        return;
    }

    cameraEl.classList.remove('attendance-scan-camera--scanning', 'attendance-scan-camera--matched', 'attendance-scan-camera--pose');

    if (state === 'scanning') {
        cameraEl.classList.add('attendance-scan-camera--scanning');
    } else if (state === 'matched') {
        cameraEl.classList.add('attendance-scan-camera--matched');
    } else if (state === 'pose') {
        cameraEl.classList.add('attendance-scan-camera--pose');
    }
}

function setPhotoInput(input, canvas) {
    return new Promise((resolve) => {
        canvas.toBlob((blob) => {
            if (!blob || !input) {
                resolve(false);
                return;
            }

            const file = new File([blob], 'face-capture.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            resolve(true);
        }, 'image/jpeg', 0.92);
    });
}

function compareDescriptors(stored, incoming) {
    if (!Array.isArray(stored) || !Array.isArray(incoming) || stored.length !== incoming.length || stored.length === 0) {
        return 0;
    }

    let dotProduct = 0;
    let normA = 0;
    let normB = 0;

    for (let i = 0; i < stored.length; i++) {
        const a = Number(stored[i]);
        const b = Number(incoming[i]);
        dotProduct += a * b;
        normA += a * a;
        normB += b * b;
    }

    if (normA <= 0 || normB <= 0) {
        return 0;
    }

    const similarity = dotProduct / (Math.sqrt(normA) * Math.sqrt(normB));

    return Math.max(0, Math.min(1, (similarity + 1) / 2));
}

function matchKnownFace(descriptor, knownFaces, threshold) {
    let bestScore = 0;
    let bestFace = null;

    for (const face of knownFaces) {
        const score = compareDescriptors(face.descriptor, descriptor);

        if (score > bestScore) {
            bestScore = score;
            bestFace = face;
        }
    }

    return {
        matched: bestScore >= threshold,
        score: bestScore,
        face: bestFace,
    };
}

function estimateHeadPose(detection) {
    const landmarks = detection.landmarks;
    const jaw = landmarks.getJawOutline();
    const nose = landmarks.getNose();
    const leftEye = landmarks.getLeftEye();
    const rightEye = landmarks.getRightEye();

    const leftCheek = jaw[3];
    const rightCheek = jaw[13];
    const chin = jaw[8];
    const faceCenterX = (leftCheek.x + rightCheek.x) / 2;
    const faceWidth = Math.max(rightCheek.x - leftCheek.x, 1);
    const noseTip = nose[3];
    const yaw = (noseTip.x - faceCenterX) / (faceWidth * 0.45);

    const eyeMidY = (leftEye[1].y + rightEye[4].y) / 2;
    const faceHeight = Math.max(chin.y - eyeMidY, 1);
    const pitch = (noseTip.y - eyeMidY) / faceHeight;

    return { yaw, pitch };
}

class FaceIdGuide {
    constructor(root) {
        this.root = root;
        this.progressRing = root?.querySelector('[data-face-id-progress]');
        this.arrowWrap = root?.querySelector('[data-face-id-arrow-wrap]');
        this.instruction = root?.querySelector('[data-face-id-instruction]');
        this.stepsEl = root?.querySelector('[data-face-id-steps]');
        this.check = root?.querySelector('[data-face-id-check]');
        this.totalSteps = 0;
        this.flashTimer = null;

        if (this.progressRing) {
            this.progressRing.style.strokeDasharray = `${RING_CIRCUMFERENCE}`;
            this.progressRing.style.strokeDashoffset = `${RING_CIRCUMFERENCE}`;
        }
    }

    isAvailable() {
        return Boolean(this.root);
    }

    show() {
        if (!this.root) {
            return;
        }

        this.root.hidden = false;
        this.root.classList.add('face-id-guide--visible');
    }

    hide() {
        if (!this.root) {
            return;
        }

        this.root.classList.remove('face-id-guide--visible');
        this.root.hidden = true;
    }

    initSteps(count) {
        this.totalSteps = count;

        if (!this.stepsEl) {
            return;
        }

        this.stepsEl.innerHTML = '';

        for (let i = 0; i < count; i++) {
            const dot = document.createElement('span');
            dot.className = 'face-id-guide__step';
            dot.dataset.stepIndex = String(i);
            this.stepsEl.appendChild(dot);
        }
    }

    reset() {
        if (this.flashTimer) {
            window.clearTimeout(this.flashTimer);
            this.flashTimer = null;
        }

        this.show();
        this.update(0, POSE_SEQUENCES.attendance[0]);
        this.setRingProgress(0);

        if (this.check) {
            this.check.hidden = true;
        }

        this.root?.classList.remove('face-id-guide--step-done');
    }

    setRingProgress(ratio) {
        if (!this.progressRing) {
            return;
        }

        const clamped = Math.max(0, Math.min(1, ratio));
        this.progressRing.style.strokeDashoffset = `${RING_CIRCUMFERENCE * (1 - clamped)}`;
    }

    update(stepIndex, step, poseStreak = 0, poseStableRequired = 2) {
        if (!this.root || !step) {
            return;
        }

        const overall = (stepIndex + Math.min(poseStreak / poseStableRequired, 1)) / Math.max(this.totalSteps, 1);
        this.setRingProgress(overall);

        if (this.instruction) {
            this.instruction.textContent = step.label;
        }

        if (this.arrowWrap) {
            this.arrowWrap.style.setProperty('--face-id-arrow-rotate', `${step.arrow}deg`);
            this.arrowWrap.dataset.pose = step.id;
        }

        this.stepsEl?.querySelectorAll('.face-id-guide__step').forEach((dot, index) => {
            dot.classList.toggle('face-id-guide__step--done', index < stepIndex);
            dot.classList.toggle('face-id-guide__step--active', index === stepIndex);
        });

        this.root.classList.toggle('face-id-guide--holding', poseStreak > 0);
    }

    flashStepComplete() {
        if (!this.root) {
            return;
        }

        this.root.classList.add('face-id-guide--step-done');

        if (this.check) {
            this.check.hidden = false;
        }

        if (this.flashTimer) {
            window.clearTimeout(this.flashTimer);
        }

        this.flashTimer = window.setTimeout(() => {
            this.root?.classList.remove('face-id-guide--step-done');

            if (this.check) {
                this.check.hidden = true;
            }
        }, 450);
    }
}

function createFaceIdGuide(cameraEl) {
    const root = cameraEl?.querySelector('[data-face-id-guide]');

    return new FaceIdGuide(root);
}

function getBranchId(config) {
    const select = document.getElementById(config.branchSelectId || 'branch-select');

    if (select?.value) {
        return select.value;
    }

    return config.defaultBranchId ?? null;
}

function getKnownFacesForBranch(config) {
    const branchId = getBranchId(config);
    const faces = config.knownFaces ?? [];

    if (!branchId) {
        return faces;
    }

    return faces.filter((face) => String(face.branch_id) === String(branchId));
}

function hasGpsReady(config) {
    if (config.requireGps === false) {
        return true;
    }

    const lat = document.getElementById(config.latitudeInputId || 'latitude')?.value;
    const lng = document.getElementById(config.longitudeInputId || 'longitude')?.value;

    return Boolean(lat && lng);
}

async function detectFace(video) {
    return faceapi
        .detectSingleFace(video, getDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();
}

async function captureAndSubmit({
    video,
    canvas,
    detection,
    descriptorInput,
    photoInput,
    form,
    config,
    statusEl,
    cameraEl,
    guide,
    matchResult,
}) {
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    descriptorInput.value = JSON.stringify(Array.from(detection.descriptor));
    await setPhotoInput(photoInput, canvas);

    if (config.methodInputId) {
        const methodInput = document.getElementById(config.methodInputId);

        if (methodInput && config.methodValue) {
            methodInput.value = config.methodValue;
        }
    }

    const successMessage = matchResult?.face?.employee_name
        ? `Wajah cocok (${matchResult.face.employee_name}). Memproses absensi...`
        : 'Wajah terdaftar. Menyimpan data...';

    guide?.hide();
    setStatus(statusEl, successMessage, 'ready');
    setCameraState(cameraEl, 'matched');
    form.submit();
}

function getPoseSequence(config) {
    if (!config.poseGuide) {
        return null;
    }

    return POSE_SEQUENCES[config.poseGuide] ?? POSE_SEQUENCES.attendance;
}

function resetScanState(scanState, guide, poseSequence) {
    scanState.poseStepIndex = 0;
    scanState.poseStreak = 0;
    scanState.poseChallengeDone = !poseSequence;
    scanState.matchStreak = 0;
    scanState.detectStreak = 0;
    scanState.lastMatchedName = null;
    scanState.isSubmitting = false;

    if (poseSequence && guide?.isAvailable()) {
        guide.initSteps(poseSequence.length);
        guide.reset();
    }
}

function startAutoScan({
    video,
    canvas,
    statusEl,
    cameraEl,
    descriptorInput,
    photoInput,
    form,
    config,
    getPaused,
    guide,
}) {
    const threshold = config.matchThreshold ?? 0.6;
    const stableFramesRequired = config.stableFramesRequired ?? 2;
    const poseStableRequired = config.poseStableRequired ?? 2;
    const scanIntervalMs = config.scanIntervalMs ?? 450;
    const enrollMode = !config.knownFaces || config.knownFaces.length === 0;
    const poseSequence = getPoseSequence(config);

    const scanState = {
        poseStepIndex: 0,
        poseStreak: 0,
        poseChallengeDone: !poseSequence,
        matchStreak: 0,
        detectStreak: 0,
        lastMatchedName: null,
        isSubmitting: false,
    };

    if (poseSequence && guide?.isAvailable()) {
        guide.initSteps(poseSequence.length);
        guide.show();
        guide.update(0, poseSequence[0], 0, poseStableRequired);
    }

    let scanTimer = null;

    async function scanTick() {
        if (getPaused() || scanState.isSubmitting) {
            return;
        }

        if (!scanState.poseChallengeDone && poseSequence) {
            const step = poseSequence[scanState.poseStepIndex];
            setCameraState(cameraEl, 'pose');
            guide?.update(scanState.poseStepIndex, step, scanState.poseStreak, poseStableRequired);

            try {
                const detection = await detectFace(video);

                if (!detection) {
                    scanState.poseStreak = 0;
                    setStatus(statusEl, 'Posisikan wajah di dalam bingkai oval.', 'scanning');
                    return;
                }

                const pose = estimateHeadPose(detection);

                if (!step.check(pose)) {
                    scanState.poseStreak = 0;
                    setStatus(statusEl, step.label, 'scanning');
                    return;
                }

                scanState.poseStreak += 1;
                setStatus(statusEl, `${step.label} (${scanState.poseStreak}/${poseStableRequired})`, 'scanning');
                guide?.update(scanState.poseStepIndex, step, scanState.poseStreak, poseStableRequired);

                if (scanState.poseStreak < poseStableRequired) {
                    return;
                }

                guide?.flashStepComplete();
                scanState.poseStepIndex += 1;
                scanState.poseStreak = 0;

                if (scanState.poseStepIndex >= poseSequence.length) {
                    scanState.poseChallengeDone = true;
                    guide?.setRingProgress(1);
                    setStatus(statusEl, enrollMode ? 'Panduan selesai. Menyimpan wajah...' : 'Memverifikasi wajah...', 'scanning');
                } else {
                    guide?.update(scanState.poseStepIndex, poseSequence[scanState.poseStepIndex], 0, poseStableRequired);
                }
            } catch (error) {
                scanState.poseStreak = 0;
                setStatus(statusEl, 'Error panduan: ' + error.message, 'error');
            }

            return;
        }

        if (!hasGpsReady(config)) {
            scanState.matchStreak = 0;
            scanState.detectStreak = 0;
            guide?.hide();
            setStatus(statusEl, 'Menunggu lokasi GPS...', 'scanning');
            setCameraState(cameraEl, 'scanning');
            return;
        }

        const knownFaces = getKnownFacesForBranch(config);

        if (!enrollMode && knownFaces.length === 0) {
            scanState.matchStreak = 0;
            setStatus(statusEl, 'Belum ada wajah terdaftar di cabang ini.', 'error');
            setCameraState(cameraEl, null);
            return;
        }

        try {
            const detection = await detectFace(video);
            setCameraState(cameraEl, 'scanning');

            if (!detection) {
                scanState.matchStreak = 0;
                scanState.detectStreak = 0;
                scanState.lastMatchedName = null;
                setStatus(statusEl, 'Posisikan wajah lurus di tengah kamera.', 'scanning');
                return;
            }

            if (enrollMode) {
                scanState.detectStreak += 1;
                setStatus(statusEl, `Memverifikasi wajah... (${scanState.detectStreak}/${stableFramesRequired})`, 'scanning');

                if (scanState.detectStreak < stableFramesRequired) {
                    return;
                }

                scanState.isSubmitting = true;
                await captureAndSubmit({
                    video,
                    canvas,
                    detection,
                    descriptorInput,
                    photoInput,
                    form,
                    config,
                    statusEl,
                    cameraEl,
                    guide,
                });

                return;
            }

            const descriptor = Array.from(detection.descriptor);
            const matchResult = matchKnownFace(descriptor, knownFaces, threshold);

            if (!matchResult.matched || !matchResult.face) {
                scanState.matchStreak = 0;
                scanState.lastMatchedName = null;
                setStatus(statusEl, 'Wajah tidak dikenali. Pastikan wajah sudah terdaftar.', 'error');
                return;
            }

            if (matchResult.face.employee_name !== scanState.lastMatchedName) {
                scanState.matchStreak = 0;
                scanState.lastMatchedName = matchResult.face.employee_name;
            }

            scanState.matchStreak += 1;
            setStatus(
                statusEl,
                `Mengenali ${matchResult.face.employee_name}... (${scanState.matchStreak}/${stableFramesRequired})`,
                'scanning',
            );

            if (scanState.matchStreak < stableFramesRequired) {
                return;
            }

            scanState.isSubmitting = true;
            await captureAndSubmit({
                video,
                canvas,
                detection,
                descriptorInput,
                photoInput,
                form,
                config,
                statusEl,
                cameraEl,
                guide,
                matchResult,
            });
        } catch (error) {
            scanState.matchStreak = 0;
            scanState.detectStreak = 0;
            setStatus(statusEl, 'Error scan: ' + error.message, 'error');
            setCameraState(cameraEl, null);
        }
    }

    scanTimer = window.setInterval(scanTick, scanIntervalMs);

    return {
        stop: () => {
            if (scanTimer) {
                window.clearInterval(scanTimer);
            }
        },
        reset: () => resetScanState(scanState, guide, poseSequence),
    };
}

async function initFaceScanner(config) {
    const video = document.getElementById(config.videoId);
    const canvas = document.getElementById(config.canvasId);
    const statusEl = document.getElementById(config.statusId);
    const captureBtn = config.captureButtonId
        ? document.getElementById(config.captureButtonId)
        : null;
    const descriptorInput = document.getElementById(config.descriptorInputId);
    const photoInput = document.getElementById(config.photoInputId);
    const form = document.getElementById(config.formId);
    const cameraEl = document.getElementById(config.cameraId || 'face-camera-wrap');
    const guide = createFaceIdGuide(cameraEl);

    if (!video || !canvas || !statusEl || !descriptorInput || !form) {
        return;
    }

    let paused = config.startPaused === true;
    let autoScanController = null;

    const getPaused = () => paused;

    window.addEventListener('face-scanner:pause', () => {
        paused = true;
        autoScanController?.reset();
        setCameraState(cameraEl, null);
    });

    window.addEventListener('face-scanner:resume', () => {
        paused = false;
    });

    try {
        await loadModels(statusEl);
        await startCamera(video, statusEl);

        if (config.autoScan !== false) {
            setStatus(statusEl, 'Ikuti panduan animasi di kamera.', 'scanning');
            setCameraState(cameraEl, 'pose');
            autoScanController = startAutoScan({
                video,
                canvas,
                statusEl,
                cameraEl,
                descriptorInput,
                photoInput,
                form,
                config,
                getPaused,
                guide,
            });
        } else {
            setStatus(statusEl, 'Kamera aktif. Posisikan wajah di tengah frame.', 'ready');

            if (captureBtn) {
                captureBtn.disabled = false;
            }
        }
    } catch (error) {
        setStatus(statusEl, 'Gagal memuat kamera/model: ' + error.message, 'error');
        return;
    }

    if (captureBtn) {
        captureBtn.addEventListener('click', async () => {
            captureBtn.disabled = true;
            setStatus(statusEl, 'Mendeteksi wajah...', 'scanning');

            try {
                const detection = await detectFace(video);

                if (!detection) {
                    setStatus(statusEl, 'Wajah tidak terdeteksi. Coba lagi dengan pencahayaan lebih baik.', 'error');
                    captureBtn.disabled = false;
                    return;
                }

                await captureAndSubmit({
                    video,
                    canvas,
                    detection,
                    descriptorInput,
                    photoInput,
                    form,
                    config,
                    statusEl,
                    cameraEl,
                    guide,
                });
            } catch (error) {
                setStatus(statusEl, 'Error: ' + error.message, 'error');
                captureBtn.disabled = false;
            }
        });
    }

    return () => {
        autoScanController?.stop();
    };
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.faceScannerConfig) {
        initFaceScanner(window.faceScannerConfig);
    }
});
