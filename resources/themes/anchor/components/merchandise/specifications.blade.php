@props(['activeVariant', 'selectedVariantStock'])

<div class="border-t border-[#E9E9E8] py-6">
    <h3 class="text-sm font-semibold text-[#37352F] mb-4">Specifications</h3>
    <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm leading-[1.6]">
        <div>
            <dt class="text-zinc-400">SKU</dt>
            <dd id="selected-sku" class="font-semibold text-[#37352F] mt-0.5">{{ $activeVariant?->sku ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-zinc-400">Weight</dt>
            <dd class="font-semibold text-[#37352F] mt-0.5">250g</dd>
        </div>
        <div>
            <dt class="text-zinc-400">Materials</dt>
            <dd class="font-semibold text-[#37352F] mt-0.5">Premium quality</dd>
        </div>
        <div>
            <dt class="text-zinc-400">Availability</dt>
            <dd id="selected-stock-fact" class="font-semibold text-[#37352F] mt-0.5">{{ $selectedVariantStock > 0 ? 'Ready to ship' : 'Sold out' }}</dd>
        </div>
    </dl>
</div>
