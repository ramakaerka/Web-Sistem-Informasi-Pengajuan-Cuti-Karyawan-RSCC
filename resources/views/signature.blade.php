<!DOCTYPE html>
<html>
<head>
    <title>Input Tanda Tangan - Laravel</title>
    <style>
        .signature-container {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        #signatureCanvas {
            border: 1px solid #000;
            background-color: #f8f8f8;
            margin: 10px 0;
        }
        .btn {
            padding: 8px 15px;
            margin-right: 10px;
            cursor: pointer;
        }
        .btn-danger { background-color: #f44336; color: white; }
        .btn-success { background-color: #4CAF50; color: white; }
    </style>
</head>
<body>
    
    <div class="signature-container">
        <h2 class="text-center">Form Tanda Tangan</h2>
        
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('manager.profileEdit.signature.upload') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <p>Silahkan tanda tangan di bawah ini:</p>
            <canvas id="signatureCanvas" width="450" height="200"></canvas>
            <input type="hidden" name="signature" id="signatureData">
            
            <div class="mt-3">
                <button type="button" id="clearButton" class="btn btn-danger">Hapus</button>
                <button type="submit" class="btn btn-success">Simpan Tanda Tangan</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signatureCanvas');
            const ctx = canvas.getContext('2d');
            const clearButton = document.getElementById('clearButton');
            const signatureInput = document.getElementById('signatureData');
            const form = document.querySelector('form');
            
            let isDrawing = false;
            
            // Setup canvas
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.strokeStyle = '#000000';
            ctx.lineWidth = 2;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            
            // Event listeners
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            // Touch support
            canvas.addEventListener('touchstart', handleTouch);
            canvas.addEventListener('touchmove', handleTouch);
            canvas.addEventListener('touchend', stopDrawing);
            
            clearButton.addEventListener('click', clearCanvas);
            
            form.addEventListener('submit', function(e) {
                if (canvas.isEmpty) {
                    e.preventDefault();
                    alert('Silahkan berikan tanda tangan terlebih dahulu!');
                } else {
                    signatureInput.value = canvas.toDataURL();
                }
            });
            
            // Functions
            function startDrawing(e) {
                isDrawing = true;
                draw(e);
            }
            
            function draw(e) {
                if (!isDrawing) return;
                
                const rect = canvas.getBoundingClientRect();
                let x, y;
                
                if (e.type.includes('touch')) {
                    x = e.touches[0].clientX - rect.left;
                    y = e.touches[0].clientY - rect.top;
                } else {
                    x = e.clientX - rect.left;
                    y = e.clientY - rect.top;
                }
                
                ctx.lineTo(x, y);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(x, y);
            }
            
            function handleTouch(e) {
                e.preventDefault();
                if (e.type === 'touchstart') {
                    startDrawing(e);
                } else if (e.type === 'touchmove') {
                    draw(e);
                }
            }
            
            function stopDrawing() {
                isDrawing = false;
                ctx.beginPath();
            }
            
            function clearCanvas() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
            }
            
            // Add isEmpty property to canvas
            Object.defineProperty(canvas, 'isEmpty', {
                get: function() {
                    const blank = document.createElement('canvas');
                    blank.width = canvas.width;
                    blank.height = canvas.height;
                    return canvas.toDataURL() === blank.toDataURL();
                }
            });
        });
    </script>
</body>
</html>