@props(['galleryItems', 'mainImageUrl', 'productName'])

<div>
    <!-- Large Hero Image -->
    <div class="aspect-square bg-[#e7e0eb]/30 border border-[#E9E9E8] rounded-xl overflow-hidden flex items-center justify-center p-12">
        @if ($mainImageUrl)
            <img id="main-product-image" class="w-full h-full object-contain mix-blend-multiply" src="{{ $mainImageUrl }}" alt="{{ $productName }}">
        @else
            <div class="text-[#979A9B] flex flex-col items-center">
                <span class="material-symbols-outlined text-6xl mb-2">image</span>
                <span class="text-xs uppercase tracking-wider font-semibold">No Image Available</span>
            </div>
        @endif
    </div>

    <!-- Thumbnail Gallery -->
    @if ($galleryItems->count() > 1)
        <div class="grid grid-cols-4 gap-4 mt-6">
            @foreach ($galleryItems as $index => $image)
                <button type="button" 
                        class="product-thumbnail aspect-square bg-[#e7e0eb]/20 border rounded-lg overflow-hidden group/thumb focus:outline-none transition {{ $index === 0 ? 'border-[#df1c24] ring-1 ring-[#df1c24]' : 'border-[#E9E9E8] hover:border-[#df1c24]/50' }}"
                        data-image-url="{{ $image['url'] }}"
                        data-variant-id="{{ $image['variant_id'] }}">
                    <img class="w-full h-full object-contain mix-blend-multiply group-hover/thumb:scale-105 transition-transform duration-300" src="{{ $image['url'] }}" alt="Preview">
                </button>
            @endforeach
        </div>
    @endif
</div>
