@extends('layouts.web')

@section('title', 'Inicio — Tu Tienda Online')
@section('meta_description', 'Lanza tu e-commerce con pagos, gestión de productos y envíos.')

@section('content')
 <!-- Hero -->
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 via-white to-white"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div>
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-100 text-brand-700 ring-1 ring-brand-200">
            Nuevo • Plataforma 100% administrable
          </span>
          <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold leading-tight">
            Crea tu <span class="text-brand-700">tienda en línea</span> en minutos
          </h1>
          <p class="mt-4 text-slate-600 text-lg">
            Acepta pagos, gestiona productos, controla envíos y crece con herramientas de marketing, todo desde una sola plataforma.
          </p>
          <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a href="demo.html" class="px-6 py-3 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-700 text-center">Ver demos</a>
            <a href="#caracteristicas" class="px-6 py-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-center">Explorar características</a>
          </div>
          <div class="mt-6 flex items-center gap-4 text-sm text-slate-600">
            <div class="flex -space-x-2">
              <img class="h-8 w-8 rounded-full ring-2 ring-white" src="https://i.pravatar.cc/64?img=3" alt="Cliente 1">
              <img class="h-8 w-8 rounded-full ring-2 ring-white" src="https://i.pravatar.cc/64?img=5" alt="Cliente 2">
              <img class="h-8 w-8 rounded-full ring-2 ring-white" src="https://i.pravatar.cc/64?img=8" alt="Cliente 3">
            </div>
            <span>+1,200 tiendas creadas este año</span>
          </div>
        </div>
        <div class="relative">
          <div class="aspect-[16/10] rounded-2xl border border-slate-200 shadow-sm overflow-hidden bg-white">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=1600&auto=format&fit=crop" alt="Dashboard vista previa">
          </div>
          <div class="absolute -bottom-6 -right-6 bg-white rounded-2xl border border-slate-200 shadow p-4 hidden sm:flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-brand-600 text-white grid place-items-center font-bold">⚡</div>
            <div>
              <p class="text-sm font-semibold">Lanza en menos de 1 día</p>
              <p class="text-xs text-slate-500">Plantillas listas para producción</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Logos -->
  <section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <p class="text-center text-xs uppercase tracking-wider text-slate-500 mb-6">Tecnologías & Integraciones</p>
      <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-6 items-center opacity-70">
        <img class="h-6 mx-auto" src="https://upload.wikimedia.org/wikipedia/commons/d/d5/Tailwind_CSS_Logo.svg" alt="Tailwind">
        <img class="h-6 mx-auto" src="https://upload.wikimedia.org/wikipedia/commons/a/a7/React-icon.svg" alt="React">
        <img class="h-6 mx-auto" src="https://upload.wikimedia.org/wikipedia/commons/9/96/Stripe_Logo%2C_revised_2016.svg" alt="Stripe">
        <img class="h-6 mx-auto" src="https://upload.wikimedia.org/wikipedia/commons/5/5a/PayPal.svg" alt="PayPal">
        <img class="h-6 mx-auto" src="https://upload.wikimedia.org/wikipedia/commons/2/22/Google_Analytics_4_logo.svg" alt="GA4">
        <img class="h-6 mx-auto" src="https://upload.wikimedia.org/wikipedia/commons/0/08/Meta_Platforms_Inc._logo.svg" alt="Meta">
      </div>
    </div>
  </section>

  <!-- Características -->
  <section id="caracteristicas" class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">🛍️</div>
          <h3 class="font-semibold text-lg">Catálogo potente</h3>
          <p class="text-slate-600 mt-1">Variantes, inventario, colecciones y carga masiva por CSV.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">💳</div>
          <h3 class="font-semibold text-lg">Pagos seguros</h3>
          <p class="text-slate-600 mt-1">Integraciones con Stripe, PayPal y pasarelas locales.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">🚚</div>
          <h3 class="font-semibold text-lg">Envíos simplificados</h3>
          <p class="text-slate-600 mt-1">Zonas, tarifas automáticas y seguimiento en tiempo real.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">📈</div>
          <h3 class="font-semibold text-lg">Marketing & SEO</h3>
          <p class="text-slate-600 mt-1">URLs amigables, cupones, campañas y recuperación de carritos.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">🔒</div>
          <h3 class="font-semibold text-lg">Nivel empresarial</h3>
          <p class="text-slate-600 mt-1">Seguridad, roles y permisos, auditoría, copias de seguridad.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">⚙️</div>
          <h3 class="font-semibold text-lg">API & extensiones</h3>
          <p class="text-slate-600 mt-1">API REST para integrarte con ERPs, CRMs y apps externas.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Cómo funciona -->
  <section id="como-funciona">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid lg:grid-cols-3 gap-8 items-start">
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold">Lanza en 3 pasos</h2>
          <p class="mt-2 text-slate-600">Sin código, sin complicaciones. Nosotros te acompañamos.</p>
          <a href="demo.html" class="inline-flex items-center gap-2 mt-4 text-brand-700 font-semibold">Ver demo de tienda
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
          </a>
        </div>
        <div class="lg:col-span-2 grid sm:grid-cols-3 gap-6">
          <div class="p-6 rounded-2xl border border-slate-200">
            <div class="text-3xl">1</div>
            <h3 class="mt-2 font-semibold">Configura tu marca</h3>
            <p class="text-slate-600 mt-1">Logo, colores, dominio y métodos de pago.</p>
          </div>
          <div class="p-6 rounded-2xl border border-slate-200">
            <div class="text-3xl">2</div>
            <h3 class="mt-2 font-semibold">Carga tus productos</h3>
            <p class="text-slate-600 mt-1">Desde Excel/CSV o de forma individual.</p>
          </div>
          <div class="p-6 rounded-2xl border border-slate-200">
            <div class="text-3xl">3</div>
            <h3 class="mt-2 font-semibold">Publica y vende</h3>
            <p class="text-slate-600 mt-1">Gestiona pedidos y envíos desde tu panel.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Planes -->
  <section id="planes" class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-center">Planes simples, sin letra chica</h2>
      <p class="text-center text-slate-600 mt-2">Empieza gratis y escala cuando estés listo.</p>
      <div class="mt-8 grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <h3 class="font-semibold text-lg">Inicio</h3>
          <p class="text-slate-600 mt-1">Ideal para validar tu idea.</p>
          <p class="mt-4"><span class="text-3xl font-extrabold">$0</span> <span class="text-slate-500">/mes</span></p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li>• 25 productos</li>
            <li>• Pasarela de pagos</li>
            <li>• Plantilla básica</li>
          </ul>
          <a href="demo.html" class="mt-6 block text-center px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800">Probar demo</a>
        </div>
        <div class="p-6 rounded-2xl bg-white border-2 border-brand-600 shadow-md">
          <h3 class="font-semibold text-lg">Profesional</h3>
          <p class="text-slate-600 mt-1">Para marcas en crecimiento.</p>
          <p class="mt-4"><span class="text-3xl font-extrabold">$19</span> <span class="text-slate-500">/mes</span></p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li>• Productos ilimitados</li>
            <li>• Temas avanzados</li>
            <li>• Descuentos y cupones</li>
            <li>• Analítica y reportes</li>
          </ul>
          <a href="demo.html" class="mt-6 block text-center px-4 py-2 rounded-xl bg-brand-600 text-white hover:bg-brand-700">Empezar</a>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <h3 class="font-semibold text-lg">Empresarial</h3>
          <p class="text-slate-600 mt-1">Con soporte dedicado.</p>
          <p class="mt-4"><span class="text-3xl font-extrabold">$99</span> <span class="text-slate-500">/mes</span></p>
          <ul class="mt-4 space-y-2 text-sm text-slate-600">
            <li>• SLA y alta disponibilidad</li>
            <li>• Usuarios y permisos</li>
            <li>• Integraciones a medida</li>
          </ul>
          <a href="#" class="mt-6 block text-center px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50">Hablar con ventas</a>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA final -->
  <section class="relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 p-8 sm:p-12 bg-gradient-to-br from-slate-50 to-brand-50">
        <div class="max-w-2xl">
          <h3 class="text-2xl sm:text-3xl font-extrabold">¿Listo para vender?</h3>
          <p class="mt-2 text-slate-600">Publica tu primera colección hoy mismo y acepta pagos en minutos.</p>
          <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="demo.html" class="px-6 py-3 rounded-xl bg-slate-900 text-white hover:bg-slate-800">Probar demo</a>
            <a href="#" class="px-6 py-3 rounded-xl border border-slate-300 hover:bg-slate-50">Agendar una demo guiada</a>
          </div>
        </div>
        <img class="hidden md:block absolute -right-10 -bottom-10 w-72 opacity-80" src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1200&auto=format&fit=crop" alt="Ilustración" />
      </div>
    </div>
  </section>

@endsection