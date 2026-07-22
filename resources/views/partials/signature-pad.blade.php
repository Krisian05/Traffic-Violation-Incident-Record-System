@props([
    'inputName' => 'signature_photo',
    'padId'     => 'sig_pad_' . uniqid(),
    'label'     => 'Digital Signature (Violator / Officer Acknowledgment)',
    'required'  => false,
])

<div class="mb-3" id="{{ $padId }}_wrap">
    <label class="mob-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    
    <div style="background: #fff; border: 1.5px dashed #cbd5e1; border-radius: 14px; padding: 8px; position: relative;">
        <canvas id="{{ $padId }}_canvas"
                style="width: 100%; height: 150px; display: block; border-radius: 10px; background: #fafafa; touch-action: none; cursor: crosshair;"></canvas>
        
        <input type="hidden" name="{{ $inputName }}" id="{{ $padId }}_input" value="">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; padding: 0 4px;">
            <span id="{{ $padId }}_hint" style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">
                <i class="ph ph-hand-drawing"></i> Sign inside box above
            </span>
            <button type="button" id="{{ $padId }}_clear" class="btn btn-sm btn-light text-danger" style="font-size: 0.72rem; font-weight: 700; border-radius: 8px;">
                <i class="ph ph-trash"></i> Clear Signature
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function initSigPad() {
        var canvas = document.getElementById('{{ $padId }}_canvas');
        var input  = document.getElementById('{{ $padId }}_input');
        var clearBtn = document.getElementById('{{ $padId }}_clear');
        var hint   = document.getElementById('{{ $padId }}_hint');
        if (!canvas || !input) return;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var hasDrawn = false;
        var lastX = 0;
        var lastY = 0;

        function resizeCanvas() {
            var rect = canvas.getBoundingClientRect();
            if (rect.width === 0) return;
            // Store current contents if drawn
            var temp = hasDrawn ? canvas.toDataURL() : null;

            canvas.width = rect.width * (window.devicePixelRatio || 1);
            canvas.height = (rect.height || 150) * (window.devicePixelRatio || 1);
            ctx.scale(window.devicePixelRatio || 1, window.devicePixelRatio || 1);
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#1e3a8a';

            if (temp) {
                var img = new Image();
                img.onload = function() { ctx.drawImage(img, 0, 0, rect.width, rect.height); };
                img.src = temp;
            }
        }

        window.addEventListener('resize', resizeCanvas);
        setTimeout(resizeCanvas, 100);

        function getPos(e) {
            var rect = canvas.getBoundingClientRect();
            var clientX = e.touches ? e.touches[0].clientX : e.clientX;
            var clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDraw(e) {
            e.preventDefault();
            drawing = true;
            var pos = getPos(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            var pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
            hasDrawn = true;
            saveSig();
        }

        function stopDraw(e) {
            if (drawing) {
                drawing = false;
                saveSig();
            }
        }

        function saveSig() {
            if (!hasDrawn) {
                input.value = '';
                return;
            }
            input.value = canvas.toDataURL('image/png');
            if (hint) {
                hint.textContent = '✓ Signature captured';
                hint.style.color = '#15803d';
            }
        }

        function clearSig() {
            var rect = canvas.getBoundingClientRect();
            ctx.clearRect(0, 0, rect.width, rect.height);
            hasDrawn = false;
            input.value = '';
            if (hint) {
                hint.innerHTML = '<i class="ph ph-hand-drawing"></i> Sign inside box above';
                hint.style.color = '#94a3b8';
            }
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDraw);

        if (clearBtn) clearBtn.addEventListener('click', clearSig);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSigPad);
    } else {
        initSigPad();
    }
})();
</script>
@endpush
