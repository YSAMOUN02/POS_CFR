<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">


    <link rel="stylesheet" href="{{ URL('assets/css/fonts6/css/all.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/icon/download.jpg') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
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
        class="fixed z-40 w-full overflow-y-auto bg-amber-400 border-t border-default rounded-t-base transition-transform bottom-0 left-0 right-0 translate-y-full bottom-[60px]"
        tabindex="-1" aria-labelledby="drawer-swipe-label">
        <div id="drawer-body" class="p-4 cursor-pointer hover:bg-amber-500" data-drawer-toggle="drawer-swipe">
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

            <button id="sale_data" data-modal-target="default-modal-sale-list"
                data-modal-toggle="default-modal-sale-list">
                <div
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <svg class="w-7 h-7 inline text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6.025A7.5 7.5 0 1 0 17.975 14H10V6.025Z" />
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.5 3c-.169 0-.334.014-.5.025V11h7.975c.011-.166.025-.331.025-.5A7.5 7.5 0 0 0 13.5 3Z" />
                        </svg>
                    </div>
                    <div class="font-medium text-center text-body">របាយការណ៍ការលក់
                    </div>
                </div>
            </button>
            {{-- <button id="openWarehouseModel" data-modal-target="default-modal-warehouse" class="hidden"
                data-modal-toggle="default-modal-warehouse">
                <div
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div class="font-medium text-center text-body">គ្រប់គ្រង ឃ្លាំង
                    </div>
                </div>
            </button> --}}
            <div id="openWarehouseModel" data-modal-target="default-modal-warehouse"
                data-modal-toggle="default-modal-warehouse"
                class=" p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                <div
                    class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <div class="font-medium text-center text-body">គ្រប់គ្រងស្តុក</div>
            </div>


            <div id="openProductModal" data-modal-target="default-modal-product-list"
                data-modal-toggle="default-modal-product-list"
                class=" p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium lg:block">
                <div
                    class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                    <i class="fa-solid fa-box-open"></i>
                </div>
                <div class=" font-medium text-center text-body">គ្រប់គ្រង ផលិតផល</div>
            </div>
            <div
                class="hidden p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                <div
                    class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                    <svg class="w-7 h-7 inline text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6h8m-8 6h8m-8 6h8M4 16a2 2 0 1 1 3.321 1.5L4 20h5M4 5l2-1v6m-2 0h4" />
                    </svg>
                </div>
                <div class="font-medium text-center text-body">គ្រប់គ្រង QUOTE</div>
            </div>

            <div
                class="hidden p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium lg:block">
                <div
                    class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                    <svg class="w-7 h-7 inline text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-3 5h3m-6 0h.01M12 16h3m-6 0h.01M10 3v4h4V3h-4Z" />
                    </svg>
                </div>
                <div class="font-medium text-center text-body">Task</div>
            </div>

            <button id="openCustomerModal" data-modal-target="default-modal-customer-list"
                data-modal-toggle="default-modal-customer-list">
                <div
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <i class="fa-solid fa-users-between-lines"></i>
                    </div>
                    <div class="font-medium text-center text-body">គ្រប់គ្រងអតិថិជន
                    </div>
                </div>
            </button>

            <div data-modal-target="static-modal-currency-exchange" data-modal-toggle="static-modal-currency-exchange"
                class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                <button>
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <svg class="w-7 h-7 inline text-body" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                    </div>
                    <div class="font-medium text-center text-body">អត្រាប្ដូរប្រាក់</div>
                </button>
            </div>
            <button id="user_data" data-modal-target="default-modal-user-list"
                data-modal-toggle="default-modal-user-list">
                <div
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
                    <div
                        class="flex justify-center items-center p-2 mx-auto mb-2 bg-neutral-primary-strong border border-default-strong rounded-full w-12 h-12">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div class="font-medium text-center text-body">គ្រប់គ្រងអ្នកប្រើប្រាស់
                    </div>
                </div>
            </button>
            <button id="purchasing">
                <div
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
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
                    class="p-4 rounded-base cursor-pointer bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium">
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

    <div id="welcomeScreen" class="fixed inset-0 flex items-center justify-center bg-amber-500 z-50">
        <h1 class="text-4xl font-bold mb-4 animate-bounce">Welcome {{ Auth::user()->name }}! to POS System 🍕🥤</h1>
        <br>
    </div>
    <main>
        @yield('content')




    </main>


    {{-- ALL modals will be printed here --}}
    @stack('modals')



    <livewire:scripts />
    <script>
        const warehouse_ids = @json(Auth::user()->warehouses->pluck('id'));
        // Add fade-out before reload/navigation
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href && !this.target) {
                    e.preventDefault();
                    document.body.classList.add('fade-out');
                    setTimeout(() => {
                        window.location = this.href;
                    }, 300);
                }
            });
        });
    </script>
    <script src="{{ asset('assets/js/flowbite.min.js') }}"></script>
    <script src="{{ asset('assets/js/html2pdf.bundle.min.js') }}"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script> --}}
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> --}}
    <script src="{{ asset('assets/js/script.js') }}"></script>

</body>

</html>
