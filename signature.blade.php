<!-- resources/views/signatures/create.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4>Signature Pad</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form id="signature-form" action="{{ route('signatures.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="signature_image" id="signature-data">
                        
                        <div class="mb-3">
                            <label class="form-label">Please sign below:</label>
                            <div class="signature-pad-container">
                                <canvas id="signature-pad"></canvas>
                            </div>
                            <div class="error-message" id="signature-error">Signature is required. Please sign above.</div>
                            <div class="signature-pad-actions">
                                <button type="button" class="btn btn-secondary" id="clear-signature">Clear</button>
                            </div>
                        </div>
                        
                        <div class="preview-container" id="preview-container">
                            <h5>Signature Preview:</h5>
                            <img id="signature-preview" src="" alt="Signature Preview">
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Submit Signature</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .signature-pad-container {
        position: relative;
        width: 100%;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: #fff;
        margin-bottom: 1rem;
    }
    
    #signature-pad {
        width: 100%;
        height: 200px;
        touch-action: none;
    }
    
    .signature-pad-actions {
        margin-top: 10px;
    }
    
    .preview-container {
        display: none;
        margin-top: 20px;
    }
    
    #signature-preview {
        max-width: 100%;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }
    
    .error-message {
        color: #dc3545;
        display: none;
        margin-top: 5px;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        const form = document.getElementById('signature-form');
        const clearButton = document.getElementById('clear-signature');
        const signatureDataInput = document.getElementById('signature-data');
        const signatureError = document.getElementById('signature-error');
        const previewContainer = document.getElementById('preview-container');
        const signaturePreview = document.getElementById('signature-preview');
        
        // Initialize canvas and context
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;
        
        // Resize canvas to fill parent container
        function resizeCanvas() {
            const containerWidth = canvas.parentElement.clientWidth;
            canvas.width = containerWidth;
            canvas.height = 200; // Fixed height or adjust as needed
            
            // Set canvas styles after resize
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#000';
        }
        
        // Initial resize
        resizeCanvas();
        
        // Resize on window resize
        window.addEventListener('resize', resizeCanvas);
        
        // Drawing functions
        function startDrawing(e) {
            isDrawing = true;
            [lastX, lastY] = getCoordinates(e);
            // Prevent scrolling on touch devices
            e.preventDefault();
        }
        
        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            
            const [currentX, currentY] = getCoordinates(e);
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(currentX, currentY);
            ctx.stroke();
            
            [lastX, lastY] = [currentX, currentY];
            
            // Hide error message when user starts drawing
            signatureError.style.display = 'none';
        }
        
        function stopDrawing() {
            isDrawing = false;
        }
        
        // Get coordinates for both mouse and touch events
        function getCoordinates(event) {
            let clientX, clientY;
            
            // Touch event
            if (event.touches && event.touches.length > 0) {
                const rect = canvas.getBoundingClientRect();
                clientX = event.touches[0].clientX - rect.left;
                clientY = event.touches[0].clientY - rect.top;
            } 
            // Mouse event
            else {
                const rect = canvas.getBoundingClientRect();
                clientX = event.clientX - rect.left;
                clientY = event.clientY - rect.top;
            }
            
            return [clientX, clientY];
        }
        
        // Clear canvas
        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            signatureDataInput.value = '';
            previewContainer.style.display = 'none';
        }
        
        // Check if canvas is empty
        function isCanvasEmpty() {
            const pixelBuffer = new Uint32Array(
                ctx.getImageData(0, 0, canvas.width, canvas.height).data.buffer
            );
            return !pixelBuffer.some(color => color !== 0);
        }
        
        // Convert canvas to image data
        function saveSignature() {
            if (isCanvasEmpty()) {
                signatureError.style.display = 'block';
                return false;
            }
            
            // Get data URL from canvas and set to hidden input
            const dataURL = canvas.toDataURL('image/png');
            signatureDataInput.value = dataURL;
            
            // Show preview
            signaturePreview.src = dataURL;
            previewContainer.style.display = 'block';
            
            return true;
        }
        
        // Event listeners for mouse
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // Event listeners for touch
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
        
        // Clear button
        clearButton.addEventListener('click', clearCanvas);
        
        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (saveSignature()) {
                // Show preview before actual submit
                setTimeout(() => {
                    this.submit();
                }, 500);
            }
        });
    });
</script>
@endsection