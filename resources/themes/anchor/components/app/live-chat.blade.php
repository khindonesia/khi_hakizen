<div x-data="chatApp()" class="fixed bottom-6 right-6 z-50 font-sans">

    <!-- BUTTON -->
    <button x-show="!open" @click="open = true"
        class="w-14 h-14 rounded-full shadow-lg
               bg-red-600 hover:bg-red-700
               text-white flex items-center justify-center
               transition">
        💬
    </button>

    <!-- CHAT BOX -->
    <div x-show="open" x-transition
        class="w-[380px] h-[520px] rounded-2xl overflow-hidden shadow-2xl
               bg-white/80 backdrop-blur-xl border border-white/40
               flex flex-col">

        <!-- HEADER (match your theme) -->
        <div class="bg-red-600 text-white px-4 py-3 flex justify-between items-center">
            <div class="font-semibold text-sm tracking-wide">
                Sobat Historiana
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white">
                ✕
            </button>
        </div>

        <!-- MESSAGES -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gradient-to-b from-white to-red-50/30">

            <template x-for="msg in messages" :key="msg.id">
                <div class="flex" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

                    <div class="max-w-[80%] text-sm leading-relaxed px-3 py-2 rounded-2xl shadow-sm"
                        :class="msg.role === 'user' ?
                            'bg-red-600 text-white rounded-br-sm' :
                            'bg-white border border-gray-100 text-gray-800 rounded-bl-sm'">
                        <span x-text="msg.content"></span>
                    </div>

                </div>
            </template>

            <!-- typing -->
            <div x-show="loading" class="text-xs text-gray-400 px-2">
                Sobat Historiana sedang mengetik...
            </div>

        </div>

        <!-- INPUT -->
        <div class="p-3 border-t bg-white flex gap-2">

            <input x-model="input" @keydown.enter="sendMessage"
                class="flex-1 text-sm px-3 py-2 rounded-xl
                       border border-gray-200
                       focus:outline-none focus:ring-2 focus:ring-red-500"
                placeholder="Tulis pertanyaan sejarah..." />

            <button @click="sendMessage"
                class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700
                       text-white text-sm transition">
                Kirim
            </button>

        </div>

    </div>
</div>

<script>
    function chatApp() {
        return {
            open: false,
            input: '',
            loading: false,
            messages: [{
                id: 1,
                role: 'assistant',
                content: 'Halo sobat, selamat datang di Komunitas Historia Indonesia (KHI). Ada yang bisa saya bantu?'
            }],

            async sendMessage() {
                if (!this.input.trim()) return;

                let userMsg = {
                    id: Date.now(),
                    role: 'user',
                    content: this.input
                };

                this.messages.push(userMsg);

                let text = this.input;
                this.input = '';
                this.loading = true;

                try {
                    let res = await fetch('/ai-chat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            message: text
                        })
                    });

                    let data = await res.json();

                    this.messages.push({
                        id: Date.now() + 1,
                        role: 'assistant',
                        content: data.reply
                    });

                } catch (e) {
                    this.messages.push({
                        id: Date.now() + 2,
                        role: 'assistant',
                        content: 'Sobat Historiana mengalami gangguan teknis :('
                    });
                }

                this.loading = false;

                this.$nextTick(() => {
                    let el = this.$el.querySelector('.overflow-y-auto');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            }
        }
    }
</script>
