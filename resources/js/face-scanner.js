const MODEL_URL = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';

async function waitForFaceApi() {
    while (typeof faceapi === 'undefined') {
        await new Promise((resolve) => setTimeout(resolve, 100));
    }
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
    statusEl.textContent = 'Kamera aktif. Posisikan wajah di tengah frame.';
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

async function initFaceScanner(config) {
    const video = document.getElementById(config.videoId);
    const canvas = document.getElementById(config.canvasId);
    const statusEl = document.getElementById(config.statusId);
    const captureBtn = document.getElementById(config.captureButtonId);
    const descriptorInput = document.getElementById(config.descriptorInputId);
    const photoInput = document.getElementById(config.photoInputId);
    const form = document.getElementById(config.formId);

    if (!video || !canvas || !statusEl || !captureBtn || !descriptorInput || !form) {
        return;
    }

    try {
        await loadModels(statusEl);
        await startCamera(video, statusEl);
        statusEl.textContent = 'Kamera aktif. Posisikan wajah di tengah frame.';
        statusEl.classList.add('attendance-scan-status--ready');
        captureBtn.disabled = false;
    } catch (error) {
        statusEl.textContent = 'Gagal memuat kamera/model: ' + error.message;
        statusEl.classList.add('attendance-scan-status--error');
        return;
    }

    captureBtn.addEventListener('click', async () => {
        captureBtn.disabled = true;
        statusEl.textContent = 'Mendeteksi wajah...';

        try {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                statusEl.textContent = 'Wajah tidak terdeteksi. Coba lagi dengan pencahayaan lebih baik.';
                statusEl.classList.remove('attendance-scan-status--ready');
                statusEl.classList.add('attendance-scan-status--error');
                captureBtn.disabled = false;
                return;
            }

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

            statusEl.textContent = 'Wajah berhasil di-scan. Mengirim data...';
            statusEl.classList.remove('attendance-scan-status--error');
            statusEl.classList.add('attendance-scan-status--ready');
            form.submit();
        } catch (error) {
            statusEl.textContent = 'Error: ' + error.message;
            statusEl.classList.add('attendance-scan-status--error');
            captureBtn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.faceScannerConfig) {
        initFaceScanner(window.faceScannerConfig);
    }
});
