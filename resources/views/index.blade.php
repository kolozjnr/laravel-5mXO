<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>
      Dashboard | TailAdmin - Tailwind CSS Admin Dashboard Template
    </title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- <link rel="stylesheet" href="./assets/css/tailwind.css" /> -->
  </head>
  <body
    x-data="{ page: 'dashboard', 'loaded': true, 'darkMode': false, 'stickyMenu': false, 'sidebarToggle': false, 'scrollTop': false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
  >
    <!-- ===== Preloader Start ===== -->
    <include src="./partials/preloader.html"></include>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
      <!-- ===== Sidebar Start ===== -->
      <include src="./partials/sidebar.html"></include>
      <!-- ===== Sidebar End ===== -->

      <!-- ===== Content Area Start ===== -->
      <div
        class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto"
      >
        <!-- Small Device Overlay Start -->
        <include src="./partials/overlay.html" />
        <!-- Small Device Overlay End -->

        <!-- ===== Header Start ===== -->
        <include src="./partials/header.html" />
        <!-- ===== Header End ===== -->

        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
            <div class="grid grid-cols-12 gap-4 md:gap-6">
              <div class="col-span-12 space-y-6 xl:col-span-12">
                <!-- Metric Group One -->
                <include src="./partials/metric-group/metric-group-01.html" />
                <!-- Metric Group One -->
              </div>


              <div class="col-span-12 space-y-6 xl:col-span-12">
                <!-- ====== Chart One Start -->
                 <div class="col-span-12 space-y-6 xl:col-span-5">
                <!-- <include src="./partials/chart/chart-01.html" /> -->
                <include src="./partials/chart/chart-03.html" />
                </div>
                <!-- ====== Chart One End -->
                 

              <div class="col-span-12">
                <!-- ====== Chart Three Start -->
                
                <!-- ====== Chart Three End -->
              </div>
              </div>
              

              <div class="col-span-12 xl:col-span-12">
                <!-- ====== Table One Start -->
                <include src="./partials/table/table-01.html" />
                <!-- ====== Table One End -->
              </div>
            </div>
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->
  </body>
</html>
