<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" size="lg">
                💾 บันทึก
            </x-filament::button>
        </div>
    </form>

    @if (auth()->user()->isManager())
        {{-- วาดลายเซ็นด้วยเมาส์/นิ้ว (ทางเลือกนอกเหนือจากอัปโหลด) --}}
        <div
            x-data="signaturePad()"
            class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">หรือวาดลายเซ็น</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                วาดในกรอบด้านล่างด้วยเมาส์หรือนิ้ว แล้วกด “บันทึกลายเซ็นที่วาด”
            </p>

            <canvas
                x-ref="canvas"
                width="500"
                height="200"
                class="mt-3 w-full max-w-lg touch-none rounded-lg border border-gray-300 bg-white dark:border-gray-700"
            ></canvas>

            <div class="mt-3 flex gap-2">
                <x-filament::button type="button" color="gray" size="sm" x-on:click="clearPad()">
                    ล้าง
                </x-filament::button>
                <x-filament::button type="button" color="primary" size="sm" x-on:click="savePad()">
                    บันทึกลายเซ็นที่วาด
                </x-filament::button>
            </div>
        </div>

        <script>
            function signaturePad() {
                return {
                    ctx: null,
                    drawing: false,
                    init() {
                        const c = this.$refs.canvas;
                        this.ctx = c.getContext('2d');
                        this.ctx.lineWidth = 2;
                        this.ctx.lineCap = 'round';
                        this.ctx.strokeStyle = '#111827';

                        const pos = (e) => {
                            const r = c.getBoundingClientRect();
                            const t = e.touches ? e.touches[0] : e;
                            return {
                                x: (t.clientX - r.left) * (c.width / r.width),
                                y: (t.clientY - r.top) * (c.height / r.height),
                            };
                        };
                        const start = (e) => { this.drawing = true; const p = pos(e); this.ctx.beginPath(); this.ctx.moveTo(p.x, p.y); e.preventDefault(); };
                        const move  = (e) => { if (!this.drawing) return; const p = pos(e); this.ctx.lineTo(p.x, p.y); this.ctx.stroke(); e.preventDefault(); };
                        const end   = () => { this.drawing = false; };

                        c.addEventListener('mousedown', start);
                        c.addEventListener('mousemove', move);
                        window.addEventListener('mouseup', end);
                        c.addEventListener('touchstart', start, { passive: false });
                        c.addEventListener('touchmove', move, { passive: false });
                        c.addEventListener('touchend', end);
                    },
                    clearPad() {
                        const c = this.$refs.canvas;
                        this.ctx.clearRect(0, 0, c.width, c.height);
                    },
                    savePad() {
                        const c = this.$refs.canvas;
                        const blank = document.createElement('canvas');
                        blank.width = c.width; blank.height = c.height;
                        if (c.toDataURL() === blank.toDataURL()) {
                            return; // nothing drawn
                        }
                        this.$wire.saveDrawnSignature(c.toDataURL('image/png'));
                        this.clearPad();
                    },
                };
            }
        </script>
    @endif
</x-filament-panels::page>
