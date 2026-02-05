    <div>

        <div class="bg-neutral-primary-soft block max-w-sm border border-default rounded-base shadow-xs">
            <div class="w-full flex justify-between items-center relative">
                @if (isset($product->image))
                    <img class="card_style rounded-t-base object-cover w-full" loading="lazy"
                        style="max-height: 200px; min-height: 200px;" src="assets/startic_img/{{ $product->image }}"
                        wire:click="addToCart" alt="" />
                @endif
                <i class="info fa-solid fa-circle-info" style="color: #005eff;"></i>
            </div>
            <div class="p-2 text-center">
                <span
                    class="inline-flex items-center bg-brand-softer border border-brand-subtle text-fg-brand-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                    <svg class="w-3 h-3 me-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.122 17.645a7.185 7.185 0 0 1-2.656 2.495 7.06 7.06 0 0 1-3.52.853 6.617 6.617 0 0 1-3.306-.718 6.73 6.73 0 0 1-2.54-2.266c-2.672-4.57.287-8.846.887-9.668A4.448 4.448 0 0 0 8.07 6.31 4.49 4.49 0 0 0 7.997 4c1.284.965 6.43 3.258 5.525 10.631 1.496-1.136 2.7-3.046 2.846-6.216 1.43 1.061 3.985 5.462 1.754 9.23Z" />
                    </svg>
                    Trending
                </span>
                @if ($product->discount_percent != 0)
                    <span
                        class="inline-flex items-center bg-brand-softer border border-brand-subtle text-fg-brand-strong text-xs font-medium px-1.5 py-0.5 rounded-sm">
                        <i class="fa-solid fa-tag"></i>
                        {{ $product->discount_percent }}% Off
                    </span>
                @endif
                <a href="#">
                    <h5 class="mt-3 mb-6 text-1xl font-semibold tracking-tight text-heading">

                        {{ $product->name }}
                    </h5>
                </a>


                <p>
                    <small class="text-danger"><i class="fa-solid fa-boxes-stacked"></i>:
                        {{ $product->stock_qty }}
                        Pcs</small>&ensp;
                    @if ($product->discount_percent != 0)
                        <del> {{ $product->price }} $</del> ->
                        {{ $product->price - ($product->price * $product->discount_percent) / 100 }} $
                    @else
                        {{ $product->price }} $
                    @endif
                </p>
            </div>
        </div>

    </div>
