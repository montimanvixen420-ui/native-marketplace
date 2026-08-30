<div class="flex min-h-screen bg-gray-50">
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>
  <main class="flex-1 px-5 py-8 sm:px-8">
    <div class="mx-auto max-w-2xl">
      <a href="/customer/dashboard" class="text-sm font-medium text-teal hover:underline">&larr; Back to dashboard</a>
      <h1 class="mt-3 font-display text-2xl font-semibold text-gray-900">Apply as a seller or supplier</h1>
      <p class="mt-1 text-sm text-gray-500">For marketplace safety, every application is manually reviewed before approval.</p>
      <?php if ($error): ?><div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="POST" action="/apply" enctype="multipart/form-data" class="mt-6 space-y-5 rounded-xl border border-gray-200 bg-white p-6">
        <div><label class="mb-1.5 block text-sm font-medium text-gray-700">I want to apply as</label><select name="application_role" required class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm"><option value="admin">Seller</option><option value="supplier">Supplier</option></select></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1.5 block text-sm font-medium text-gray-700">Business / shop name</label><input name="business_name" required class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm"></div><div><label class="mb-1.5 block text-sm font-medium text-gray-700">Mobile number</label><input name="phone" required class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm"></div></div>
        <div><label class="mb-1.5 block text-sm font-medium text-gray-700">Business address</label><input name="business_address" required class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm"></div>
        <div><label class="mb-1.5 block text-sm font-medium text-gray-700">What will you sell or supply?</label><textarea name="business_description" rows="3" required class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm"></textarea></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-1.5 block text-sm font-medium text-gray-700">Government ID</label><input type="file" name="government_id" accept="application/pdf,image/png,image/jpeg" required class="block w-full text-sm"><p class="mt-1 text-xs text-gray-400">PDF, PNG, or JPEG; max 5MB.</p></div><div><label class="mb-1.5 block text-sm font-medium text-gray-700">Live selfie verification</label><div class="rounded-lg border border-gray-200 bg-gray-50 p-3"><video id="selfie-video" autoplay playsinline muted class="hidden aspect-square w-full rounded-md bg-gray-900 object-cover"></video><canvas id="selfie-canvas" class="hidden"></canvas><input id="selfie-file" type="file" name="selfie" accept="image/jpeg" required class="sr-only"><button id="start-selfie-camera" type="button" class="rounded-lg border border-teal px-3 py-2 text-sm font-semibold text-teal">Open front camera</button><button id="capture-selfie" type="button" disabled class="ml-2 rounded-lg bg-ink px-3 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">Take selfie</button><p id="selfie-status" class="mt-2 text-xs text-gray-500">Use your phone's front camera. Gallery upload is not available.</p></div></div></div>
        <label class="flex gap-2 text-xs leading-5 text-gray-600"><input type="checkbox" name="consent" required class="mt-1">I consent to the use of my ID and selfie solely to review this marketplace application. I understand this is a manual review, not automated facial verification.</label>
        <button class="w-full rounded-lg bg-ink py-3 text-sm font-semibold text-white hover:bg-gray-800">Submit for review</button>
      </form>
    </div>
  </main>
</div>
<script>
(() => {
  const video = document.getElementById('selfie-video');
  const canvas = document.getElementById('selfie-canvas');
  const fileInput = document.getElementById('selfie-file');
  const startButton = document.getElementById('start-selfie-camera');
  const captureButton = document.getElementById('capture-selfie');
  const status = document.getElementById('selfie-status');
  let stream = null;

  const stopCamera = () => {
    if (stream) stream.getTracks().forEach(track => track.stop());
    stream = null;
    video.srcObject = null;
  };

  const showCameraError = error => {
    const messages = {
      NotAllowedError: 'Camera access was blocked. Check the camera permission for this site, then try again.',
      NotFoundError: 'No camera was found. Connect or enable a camera, then try again.',
      NotReadableError: 'Your camera is busy in another app or browser tab. Close it there, then try again.',
      OverconstrainedError: 'This camera does not support the front-camera setting. Try again to use the available camera.',
      SecurityError: 'Camera access is disabled by browser security settings.',
    };
    status.textContent = messages[error?.name] || 'Could not start the camera. Please try again.';
  };

  startButton.addEventListener('click', async () => {
    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
      status.textContent = 'Camera needs HTTPS (or localhost). Open this site through https:// or localhost, not a network IP address.';
      return;
    }
    stopCamera();
    startButton.disabled = true;
    captureButton.disabled = true;
    status.textContent = 'Opening camera…';
    try {
      try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'user' } }, audio: false });
      } catch (error) {
        if (error.name !== 'OverconstrainedError') throw error;
        stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
      }
      video.srcObject = stream;
      video.classList.remove('hidden');
      await video.play();
      if (!video.videoWidth || !video.videoHeight) await new Promise(resolve => video.addEventListener('loadedmetadata', resolve, { once: true }));
      captureButton.disabled = false;
      status.textContent = 'Camera ready. Center your face, then take your selfie.';
    } catch (error) {
      stopCamera();
      showCameraError(error);
    } finally {
      startButton.disabled = false;
    }
  });

  captureButton.addEventListener('click', () => {
    if (!video.videoWidth || !video.videoHeight) { status.textContent = 'The camera is still starting. Please wait a moment.'; return; }
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    canvas.toBlob(blob => {
      if (!blob || !window.DataTransfer) { status.textContent = 'Could not prepare the selfie. Please retake it.'; return; }
      const transfer = new DataTransfer();
      transfer.items.add(new File([blob], 'live-selfie.jpg', { type: 'image/jpeg' }));
      fileInput.files = transfer.files;
      stopCamera();
      video.classList.add('hidden');
      captureButton.disabled = true;
      startButton.textContent = 'Retake selfie';
      status.textContent = 'Selfie captured. You can retake it before submitting.';
    }, 'image/jpeg', 0.9);
  });

  document.querySelector('form[action="/apply"]').addEventListener('submit', event => {
    if (!fileInput.files.length) { event.preventDefault(); status.textContent = 'Please capture a live selfie using the camera.'; }
  });
  window.addEventListener('beforeunload', stopCamera);
})();
</script>
