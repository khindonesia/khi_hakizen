<?php
use App\Models\Comment;
use Livewire\Volt\Component;
use Filament\Notifications\Notification;

new class extends Component {
    public $model;
    public string $newCommentBody = '';
    public ?int $replyingToId = null;
    public string $replyBody = '';
    public int $perPage = 5;

    public function mount($model): void
    {
        $this->model = $model;
    }

    public function submitComment(): void
    {
        if (!auth()->check()) {
            return;
        }

        $this->validate([
            'newCommentBody' => 'required|min:2|max:1000'
        ], [
            'newCommentBody.required' => 'Komentar tidak boleh kosong.',
            'newCommentBody.min' => 'Komentar minimal 2 karakter.',
            'newCommentBody.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $this->model->allComments()->create([
            'user_id' => auth()->id(),
            'body' => $this->newCommentBody,
        ]);

        $this->newCommentBody = '';

        Notification::make()
            ->success()
            ->title('Komentar Terkirim')
            ->body('Terima kasih atas komentar Anda.')
            ->send();
    }

    public function toggleLike(int $commentId): void
    {
        if (!auth()->check()) {
            return;
        }

        $comment = Comment::findOrFail($commentId);
        $comment->likes()->toggle(auth()->id());
    }

    public function setReplyingTo(int $commentId): void
    {
        if (!auth()->check()) {
            return;
        }

        if ($this->replyingToId === $commentId) {
            $this->replyingToId = null;
            $this->replyBody = '';
        } else {
            $this->replyingToId = $commentId;
            $this->replyBody = '';
        }
    }

    public function submitReply(int $parentId): void
    {
        if (!auth()->check()) {
            return;
        }

        $this->validate([
            'replyBody' => 'required|min:2|max:1000'
        ], [
            'replyBody.required' => 'Balasan tidak boleh kosong.',
            'replyBody.min' => 'Balasan minimal 2 karakter.',
            'replyBody.max' => 'Balasan maksimal 1000 karakter.',
        ]);

        $this->model->allComments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $parentId,
            'body' => $this->replyBody,
        ]);

        $this->replyingToId = null;
        $this->replyBody = '';

        Notification::make()
            ->success()
            ->title('Balasan Terkirim')
            ->body('Balasan Anda telah diterbitkan.')
            ->send();
    }

    public function loadMore(): void
    {
        $this->perPage += 5;
    }

    public function getCommentsCount(): int
    {
        return $this->model->allComments()->count();
    }

    public function getComments()
    {
        return $this->model->comments()
            ->with(['user', 'replies.user', 'replies.likes', 'likes'])
            ->latest()
            ->take($this->perPage)
            ->get();
    }
}
?>

@volt('comments')
<div class="bg-white border border-[#E9E9E8] rounded-3xl p-6 md:p-8 shadow-sm flex flex-col gap-6 font-['Inter']">
    <!-- Header -->
    <div class="mb-4 border-b border-[#E9E9E8] pb-2">
        <h3 class="text-[22px] font-semibold leading-[1.30] text-[#020611]">Komentar ({{ $this->getCommentsCount() }})</h3>
    </div>

    <!-- Comment Input Area -->
    @auth
        <div class="flex gap-3 mb-6">
            <div class="w-10 h-10 shrink-0 rounded-full overflow-hidden border border-[#E9E9E8] bg-[#ede6f1] flex items-center justify-center">
                @if(auth()->user()->getRawOriginal('avatar'))
                    <img src="{{ Storage::url(auth()->user()->getRawOriginal('avatar')) }}" alt="Avatar" class="w-full h-full object-cover">
                @else
                    <span class="text-[#575e75] font-semibold text-lg uppercase">{{ substr(auth()->user()->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="flex-1 flex flex-col gap-2">
                <textarea wire:model="newCommentBody" 
                          class="w-full border border-[#D1D1D0] rounded-lg p-3 text-base leading-[1.55] text-[#37352F] bg-white focus:outline-none focus:border-[#df1c24] focus:ring-1 focus:ring-[#df1c24] transition-all resize-none placeholder:text-[#979A9B]" 
                          placeholder="Bagikan pemikiran Anda tentang ini..." 
                          rows="3"></textarea>
                @error('newCommentBody') <span class="text-xs text-red-500 font-medium">{{ $message }}</span> @enderror
                <div class="flex justify-end">
                    <button wire:click="submitComment" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-medium text-sm px-4 py-2 rounded-lg transition duration-200 shadow-sm">
                        Kirim Komentar
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="p-6 bg-[#FFF9F5] border border-[#E9E9E8] rounded-2xl text-center mb-6">
            <p class="text-sm text-[#575e75] mb-4">Ingin bergabung dalam diskusi? Silakan masuk atau daftar anggota terlebih dahulu.</p>
            <div class="flex justify-center gap-3">
                <a href="{{ route('login') }}" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold text-xs px-5 py-2.5 rounded-xl transition duration-200">
                    Masuk
                </a>
                <a href="{{ route('join') }}" class="bg-white border border-[#E9E9E8] text-[#37352F] font-semibold text-xs px-5 py-2.5 rounded-xl hover:bg-zinc-50 transition duration-200">
                    Daftar Anggota
                </a>
            </div>
        </div>
    @endauth

    <!-- Comments List -->
    <div class="flex flex-col gap-6">
        @forelse($this->getComments() as $comment)
            <div class="flex flex-col gap-4">
                <!-- Parent Comment -->
                <div class="flex gap-3 group">
                    <div class="w-10 h-10 shrink-0 rounded-full overflow-hidden border border-[#E9E9E8] bg-[#ede6f1] flex items-center justify-center">
                        @if($comment->user->getRawOriginal('avatar'))
                            <img src="{{ Storage::url($comment->user->getRawOriginal('avatar')) }}" alt="{{ $comment->user->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-[#575e75] font-semibold text-lg uppercase">{{ substr($comment->user->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-[2px] flex-wrap">
                            <span class="text-sm font-bold text-[#020611]">{{ $comment->user->name }}</span>
                            @if($comment->isAuthor())
                                <span class="px-[6px] py-[2px] bg-[#df1c24]/10 text-[#df1c24] text-[10px] font-bold rounded-sm uppercase tracking-wider">Penulis</span>
                            @endif
                            <span class="text-[13px] leading-[1.40] text-[#979A9B] font-normal">• {{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-base leading-[1.55] text-[#37352F] mb-2 whitespace-pre-wrap">{{ $comment->body }}</p>
                        
                        <!-- Action Row -->
                        <div class="flex items-center gap-4">
                            <button wire:click="toggleLike({{ $comment->id }})" class="flex items-center gap-[4px] transition-colors group/btn {{ $comment->hasLiked(auth()->id()) ? 'text-[#df1c24]' : 'text-[#979A9B] hover:text-[#df1c24]' }}">
                                <span class="material-symbols-outlined text-[18px] {{ $comment->hasLiked(auth()->id()) ? 'icon-fill' : 'group-hover/btn:icon-fill' }}" style="{{ $comment->hasLiked(auth()->id()) ? "font-variation-settings: 'FILL' 1;" : "" }}">favorite</span>
                                <span class="text-[13px] font-semibold leading-[1.40]">{{ $comment->likes->count() }}</span>
                            </button>
                            
                            @auth
                                <button wire:click="setReplyingTo({{ $comment->id }})" class="flex items-center gap-[4px] text-[#979A9B] hover:text-[#37352F] transition-colors text-[14px] font-medium leading-[1.30]">
                                    Balas
                                </button>
                            @endauth
                        </div>
                    </div>
                </div>
                
                <!-- Nested Replies -->
                @if($comment->replies->isNotEmpty())
                    <div class="ml-6 pl-4 border-l border-[#D1D1D0] flex flex-col gap-4 mt-2">
                        @foreach($comment->replies->sortBy('created_at') as $reply)
                            <div class="flex gap-3">
                                <div class="w-8 h-8 shrink-0 rounded-full overflow-hidden border border-[#E9E9E8] bg-[#ede6f1] flex items-center justify-center">
                                    @if($reply->user->getRawOriginal('avatar'))
                                        <img src="{{ Storage::url($reply->user->getRawOriginal('avatar')) }}" alt="{{ $reply->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[#575e75] font-semibold text-xs uppercase">{{ substr($reply->user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-[2px] flex-wrap">
                                        <span class="text-[14px] font-semibold leading-[1.50] text-[#020611] font-bold">{{ $reply->user->name }}</span>
                                        @if($reply->isAuthor())
                                            <span class="px-[6px] py-[2px] bg-[#df1c24]/10 text-[#df1c24] text-[10px] font-bold rounded-sm uppercase tracking-wider">Penulis</span>
                                        @endif
                                        <span class="text-[13px] leading-[1.40] text-[#979A9B] font-normal">• {{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-base leading-[1.55] text-[#37352F] mb-2 whitespace-pre-wrap">{{ $reply->body }}</p>
                                    
                                    <!-- Reply Action (Likes) -->
                                    <div class="flex items-center gap-4">
                                        <button wire:click="toggleLike({{ $reply->id }})" class="flex items-center gap-[4px] transition-colors group/btn {{ $reply->hasLiked(auth()->id()) ? 'text-[#df1c24]' : 'text-[#979A9B] hover:text-[#df1c24]' }}">
                                            <span class="material-symbols-outlined text-[16px] {{ $reply->hasLiked(auth()->id()) ? 'icon-fill' : 'group-hover/btn:icon-fill' }}" style="{{ $reply->hasLiked(auth()->id()) ? "font-variation-settings: 'FILL' 1;" : "" }}">favorite</span>
                                            <span class="text-[13px] font-semibold leading-[1.40]">{{ $reply->likes->count() }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <!-- Reply Input Box -->
                @if($replyingToId === $comment->id)
                    @auth
                        <div class="ml-10 flex gap-3 mt-3">
                            <div class="w-8 h-8 shrink-0 rounded-full overflow-hidden border border-[#E9E9E8] bg-[#ede6f1] flex items-center justify-center">
                                @if(auth()->user()->getRawOriginal('avatar'))
                                    <img src="{{ Storage::url(auth()->user()->getRawOriginal('avatar')) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[#575e75] font-semibold text-xs uppercase">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-1 flex flex-col gap-2">
                                <textarea wire:model="replyBody" 
                                          class="w-full border border-[#D1D1D0] rounded-lg p-2.5 text-sm text-[#37352F] bg-white focus:outline-none focus:border-[#df1c24] focus:ring-1 focus:ring-[#df1c24] transition-all resize-none placeholder:text-[#979A9B]" 
                                          placeholder="Balas komentar..." 
                                          rows="2"></textarea>
                                @error('replyBody') <span class="text-xs text-red-500 font-medium">{{ $message }}</span> @enderror
                                <div class="flex justify-end gap-2">
                                    <button wire:click="$set('replyingToId', null)" class="text-xs text-[#979A9B] hover:text-[#37352F] font-semibold px-4 py-2 rounded-lg transition duration-200">
                                        Batal
                                    </button>
                                    <button wire:click="submitReply({{ $comment->id }})" class="bg-[#df1c24] hover:bg-[#c41219] text-white font-semibold text-xs px-4 py-2 rounded-lg transition duration-200 shadow-sm">
                                        Balas
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endauth
                @endif
            </div>
        @empty
            <p class="text-sm text-[#575e75] text-center py-6">Belum ada komentar. Jadilah yang pertama memberikan pemikiran!</p>
        @endforelse
    </div>

    <!-- Load More -->
    @if($this->getCommentsCount() > $this->perPage)
        <div class="mt-6 flex justify-center border-t border-[#E9E9E8] pt-4">
            <button wire:click="loadMore" class="text-[#37352F] hover:text-[#df1c24] transition-colors border border-[#D1D1D0] px-4 py-2 rounded-lg hover:bg-[#ede6f1] text-[14px] font-medium leading-[1.30]">
                Muat Komentar Lainnya
            </button>
        </div>
    @endif
</div>
@endvolt
