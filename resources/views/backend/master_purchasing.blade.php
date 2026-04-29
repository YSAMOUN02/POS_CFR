<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/icon/download.jpg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    {{-- Google Font  --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Khmer:wght@100..900&display=swap" rel="stylesheet">

    <livewire:styles />

    <title>Point Of Sales</title>
</head>

<body>
    {{-- Not Using it but kept for reference --}}
    <!-- drawer init and toggle -->
    <div class="text-center hidden">
        <button
            class="inline-flex items-center justify-center text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
            type="button" data-drawer-target="drawer-disabled-backdrop" data-drawer-show="drawer-disabled-backdrop"
            data-drawer-backdrop="false" aria-controls="drawer-disabled-backdrop">
            Show drawer without backdrop
        </button>
    </div>
    <!-- drawer init and toggle -->
    <div class="text-center hidden">
        <button
            class="inline-flex items-center justify-center text-white bg-brand box-border border border-transparent hover:bg-brand-strong focus:ring-4 focus:ring-brand-medium shadow-xs font-medium leading-5 rounded-base text-sm px-4 py-2.5 focus:outline-none"
            type="button" data-drawer-target="drawer-swipe" data-drawer-show="drawer-swipe"
            data-drawer-placement="bottom" data-drawer-edge="true" data-drawer-edge-offset="bottom-[60px]"
            aria-controls="drawer-swipe">
            Show swipeable drawer
        </button>
    </div>






    <!-- drawer component -->
    <div id="drawer-swipe"
        class="fixed z-40 w-full overflow-y-auto bg-blue-400 border-t border-default rounded-t-base transition-transform bottom-0 left-0 right-0 translate-y-full bottom-[60px]"
        tabindex="-1" aria-labelledby="drawer-swipe-label">
        <div id="drawer-body" class="p-4 cursor-pointer hover:bg-blue-500" data-drawer-toggle="drawer-swipe">
            <span class="absolute w-8 h-1 -translate-x-1/2 bg-neutral-quaternary rounded-lg top-3 left-1/2"></span>
            <h5 id="drawer-swipe-label" class="inline-flex items-center text-base text-body font-medium">
                <svg class="w-5 h-5 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                    height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14 17h6m-3 3v-6M4.857 4h4.286c.473 0 .857.384.857.857v4.286a.857.857 0 0 1-.857.857H4.857A.857.857 0 0 1 4 9.143V4.857C4 4.384 4.384 4 4.857 4Zm10 0h4.286c.473 0 .857.384.857.857v4.286a.857.857 0 0 1-.857.857h-4.286A.857.857 0 0 1 14 9.143V4.857c0-.473.384-.857.857-.857Zm-10 10h4.286c.473 0 .857.384.857.857v4.286a.857.857 0 0 1-.857.857H4.857A.857.857 0 0 1 4 19.143v-4.286c0-.473.384-.857.857-.857Z" />
                </svg>
                &ensp; មីនុយ
            </h5>
        </div>

        <div id="drawer" class="grid grid-cols-3 gap-4 p-4 lg:grid-cols-8">
              <button id="openCustomerModal" data-modal-target="default-modal-vendor-list" onclick="loadVendors(1)"
            data-modal-toggle="default-modal-vendor-list">
            <div
                class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                <div
                    class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                  <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="font-medium text-center text-body">គ្រប់គ្រង អ្នកផ្គត់ផ្គង់
                </div>
            </div>
        </button>
           <button id="openPurchaseModal" data-modal-target="default-modal-purchase-list"
           onclick="FetchPurchase(1)"
            data-modal-toggle="default-modal-purchase-list">
            <div
                class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                <div
                    class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
               <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="font-medium text-center text-body">គ្រប់គ្រង ទិន្នន័យការទិញចូល
                </div>
            </div>
        </button>
            <button id="Sales">
                <div
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <div class="font-medium text-center text-body">Home
                    </div>
                </div>
            </button>

            <button id="purchasing">
                <div
                    class="hidden p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div class="font-medium text-center text-body">ទិញចូលស្តក
                    </div>
                </div>
            </button>

            <button id="logout">
                <div
                    class="hidden p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </div>
                    <div class="font-medium text-center text-body">ចាកចេញ
                    </div>
                </div>
            </button>
        </div>
    </div>


    <main>
        @yield('content')




    </main>


    {{-- ALL modals will be printed here --}}
    @stack('modals')



    <livewire:scripts />
    <script>
        const warehouse_ids = @json(Auth::user()->warehouses->pluck('id'));
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('assets/js/script_purchase.js') }}"></script>

</body>

</html>
