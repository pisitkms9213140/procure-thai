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
        <div
            x-data="signaturePad()"
            class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
        >
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">ลายเซ็นดิจิทัล</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                ใช้ประทับบนใบสั่งซื้อ (PO) เมื่อผู้จัดการอนุมัติ — อัปโหลดรูป หรือ วาดด้วยเมาส์/นิ้ว (แนะนำ PNG พื้นหลังโปร่งใส)
            </p>

            {{-- ลายเซ็นปัจจุบัน --}}
            @if ($signatureUrl)
                <div class="mt-4">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">ลายเซ็นปัจจุบัน</p>
                    <div class="mt-1 flex items-center gap-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($signatureUrl) }}"
                             alt="signature"
                             class="h-20 rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700">
                        <x-filament::button type="button" color="danger" size="sm" wire:click="removeSignature">
                            ลบลายเซ็น
                        </x-filament::button>
                    </div>
                </div>
            @endif

            <div class="mt-4 grid gap-6 md:grid-cols-2">
                {{-- อัปโหลด --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">อัปโหลดรูปลายเซ็น</p>
                    <input type="file" x-ref="file" accept="image/png,image/jpeg" class="hidden" x-on:change="uploadFile($event)">
                    <x-filament::button type="button" color="gray" class="mt-2" x-on:click="$refs.file.click()">
                        เลือกไฟล์รูป
                    </x-filament::button>
                </div>

                {{-- วาด --}}
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">วาดลายเซ็น</p>
                    <canvas
                        x-ref="canvas"
                        width="500"
                        height="200"
                        class="mt-2 w-full max-w-md touch-none cursor-crosshair rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 dark:border-gray-600 dark:bg-gray-800"
                    ></canvas>
                    <div class="mt-2 flex gap-2">
                        <x-filament::button type="button" color="gray" size="sm" x-on:click="clearPad()">
                            ล้าง
                        </x-filament::button>
                        <x-filament::button type="button" color="primary" size="sm" x-on:click="savePad()">
                            บันทึกลายเซ็นที่วาด
                        </x-filament::button>
                    </div>
                </div>
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
                        this.ctx.lineWidth = 2.5;
                        this.ctx.lineCap = 'round';
                        this.ctx.lineJoin = 'round';
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
                        if (c.toDataURL() === blank.toDataURL()) { return; } // nothing drawn
                        this.$wire.saveSignatureData(c.toDataURL('image/png'));
                        this.clearPad();
                    },
                    uploadFile(e) {
                        const f = e.target.files[0];
                        if (!f) return;
                        const reader = new FileReader();
                        reader.onload = () => this.$wire.saveSignatureData(reader.result);
                        reader.readAsDataURL(f);
                        e.target.value = '';
                    },
                };
            }
        </script>
    @endif
</x-filament-panels::page>
