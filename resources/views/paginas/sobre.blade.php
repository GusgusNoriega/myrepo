@extends('layouts.web')

@section('title', 'Inicio — Tu Tienda Online')
@section('meta_description', 'Lanza tu e-commerce con pagos, gestión de productos y envíos.')

@section('content')
  <!-- Hero -->
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-brand-50 via-white to-white"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div>
          <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight">Impulsamos a marcas a vender online</h1>
          <p class="mt-4 text-lg text-slate-600">Creamos herramientas simples y potentes para que cualquier negocio pueda lanzar y escalar su e-commerce sin fricción.</p>
          <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="demo.html" class="px-6 py-3 rounded-xl bg-brand-600 text-white font-semibold hover:bg-brand-700 text-center">Probar demo</a>
            <a href="contacto.html" class="px-6 py-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-center">Hablar con nosotros</a>
          </div>
        </div>
        <div class="relative">
          <div class="aspect-[16/10] rounded-2xl border border-slate-200 shadow-sm overflow-hidden bg-white">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?q=80&w=1600&auto=format&fit=crop" alt="Nuestro equipo">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Métricas (placeholders para demo) -->
  <section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-4">
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl border border-slate-200 bg-white">
          <div class="text-3xl font-extrabold">200+</div>
          <div class="text-sm text-slate-600">tiendas creadas*</div>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 bg-white">
          <div class="text-3xl font-extrabold">99.9%</div>
          <div class="text-sm text-slate-600">uptime*</div>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 bg-white">
          <div class="text-3xl font-extrabold">8</div>
          <div class="text-sm text-slate-600">países en LATAM*</div>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 bg-white">
          <div class="text-3xl font-extrabold">24/5</div>
          <div class="text-sm text-slate-600">soporte*</div>
        </div>
      </div>
      <p class="text-xs text-slate-500 mt-2">*Cifras de demostración. Reemplaza por datos reales al lanzar.</p>
    </div>
  </section>

  <!-- Valores -->
  <section class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-center">Nuestros valores</h2>
      <p class="text-center text-slate-600 mt-2">Lo que guía nuestras decisiones y productos.</p>
      <div class="mt-8 grid md:grid-cols-3 gap-6">
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">🧭</div>
          <h3 class="font-semibold text-lg">Enfoque en el cliente</h3>
          <p class="text-slate-600 mt-1">Escuchamos y construimos desde la experiencia del comerciante.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">⚡</div>
          <h3 class="font-semibold text-lg">Simple y veloz</h3>
          <p class="text-slate-600 mt-1">Menos clics, más ventas. Optimizamos para el día a día.</p>
        </div>
        <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
          <div class="h-10 w-10 rounded-xl bg-brand-100 text-brand-700 grid place-items-center mb-3">🔒</div>
          <h3 class="font-semibold text-lg">Seguridad primero</h3>
          <p class="text-slate-600 mt-1">Protección de datos, buenas prácticas y monitoreo continuo.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Historia (línea de tiempo) -->
  <section>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-center">Nuestra historia</h2>
      <ol class="mt-8 relative border-s border-slate-200">
        <li class="pl-6 pb-8 relative">
          <span class="absolute -left-2 top-1.5 h-4 w-4 rounded-full bg-brand-600"></span>
          <h3 class="font-semibold">2019 — Idea</h3>
          <p class="text-slate-600 text-sm mt-1">Validamos la necesidad de un e-commerce “listo para usar” en la región.</p>
        </li>
        <li class="pl-6 pb-8 relative">
          <span class="absolute -left-2 top-1.5 h-4 w-4 rounded-full bg-brand-600"></span>
          <h3 class="font-semibold">2021 — Primeras tiendas</h3>
          <p class="text-slate-600 text-sm mt-1">Lanzamos la beta cerrada y abrimos nuestras primeras 20 tiendas.</p>
        </li>
        <li class="pl-6 pb-8 relative">
          <span class="absolute -left-2 top-1.5 h-4 w-4 rounded-full bg-brand-600"></span>
          <h3 class="font-semibold">2023 — Integraciones clave</h3>
          <p class="text-slate-600 text-sm mt-1">Agregamos pasarelas locales, logística y analítica.</p>
        </li>
        <li class="pl-6 relative">
          <span class="absolute -left-2 top-1.5 h-4 w-4 rounded-full bg-brand-600"></span>
          <h3 class="font-semibold">2025 — Escalando LATAM</h3>
          <p class="text-slate-600 text-sm mt-1">Consolidamos la plataforma y ampliamos soporte regional.</p>
        </li>
      </ol>
    </div>
  </section>

  <!-- Equipo -->
  <section class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-center">Equipo</h2>
      <p class="text-center text-slate-600 mt-2">Un equipo pequeño, productivo y obsesionado por la experiencia.</p>
      <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- 6 perfiles de ejemplo -->
        <article class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
          <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1544006659-f0b21884ce1d?q=80&w=1200&auto=format&fit=crop" alt="Miembro del equipo">
          <div class="p-4">
            <div class="font-semibold">Ana García</div>
            <div class="text-sm text-slate-600">CEO & Co‑founder</div>
            <div class="mt-2 text-xs"><a href="#" class="underline text-slate-600">LinkedIn</a> · <a href="#" class="underline text-slate-600">Twitter</a></div>
          </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
          <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1547425260-76bcadfb4f2c?q=80&w=1200&auto=format&fit=crop" alt="Miembro del equipo">
          <div class="p-4">
            <div class="font-semibold">Luis Paredes</div>
            <div class="text-sm text-slate-600">CTO & Co‑founder</div>
            <div class="mt-2 text-xs"><a href="#" class="underline text-slate-600">LinkedIn</a> · <a href="#" class="underline text-slate-600">GitHub</a></div>
          </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
          <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1524504388940-b1c1722653e1?q=80&w=1200&auto=format&fit=crop" alt="Miembro del equipo">
          <div class="p-4">
            <div class="font-semibold">María Rojas</div>
            <div class="text-sm text-slate-600">Head of Product</div>
            <div class="mt-2 text-xs"><a href="#" class="underline text-slate-600">LinkedIn</a></div>
          </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
          <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1529665253569-6d01c0eaf7b6?q=80&w=1200&auto=format&fit=crop" alt="Miembro del equipo">
          <div class="p-4">
            <div class="font-semibold">Jorge Medina</div>
            <div class="text-sm text-slate-600">Engineering Lead</div>
            <div class="mt-2 text-xs"><a href="#" class="underline text-slate-600">LinkedIn</a> · <a href="#" class="underline text-slate-600">GitHub</a></div>
          </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
          <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1554151228-14d9def656e4?q=80&w=1200&auto=format&fit=crop" alt="Miembro del equipo">
          <div class="p-4">
            <div class="font-semibold">Sofía Torres</div>
            <div class="text-sm text-slate-600">Customer Success</div>
            <div class="mt-2 text-xs"><a href="#" class="underline text-slate-600">LinkedIn</a></div>
          </div>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
          <img class="w-full h-48 object-cover" src="https://images.unsplash.com/photo-1566492031773-4f4e44671857?q=80&w=1200&auto=format&fit=crop" alt="Miembro del equipo">
          <div class="p-4">
            <div class="font-semibold">Carlos Vega</div>
            <div class="text-sm text-slate-600">Solutions Architect</div>
            <div class="mt-2 text-xs"><a href="#" class="underline text-slate-600">LinkedIn</a></div>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- Cultura y beneficios -->
  <section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid lg:grid-cols-2 gap-8 items-start">
        <div>
          <h2 class="text-2xl sm:text-3xl font-extrabold">Nuestra cultura</h2>
          <ul class="mt-4 space-y-2 text-sm text-slate-700">
            <li class="p-3 rounded-xl border border-slate-200">Transparencia y feedback continuo</li>
            <li class="p-3 rounded-xl border border-slate-200">Autonomía con responsabilidad</li>
            <li class="p-3 rounded-xl border border-slate-200">Sesiones de aprendizaje semanales</li>
            <li class="p-3 rounded-xl border border-slate-200">Remoto‑first, reuniones efectivas</li>
          </ul>
        </div>
        <div class="rounded-2xl border border-slate-200 p-6 bg-slate-50">
          <h3 class="font-semibold">Beneficios</h3>
          <div class="grid sm:grid-cols-2 gap-3 mt-3 text-sm">
            <div class="p-3 rounded-xl border border-slate-200 bg-white">Horario flexible</div>
            <div class="p-3 rounded-xl border border-slate-200 bg-white">Equipo de alto nivel</div>
            <div class="p-3 rounded-xl border border-slate-200 bg-white">Capacitación & budget</div>
            <div class="p-3 rounded-xl border border-slate-200 bg-white">Remoto/híbrido</div>
          </div>
          <a href="#jobs" class="inline-block mt-4 px-4 py-2 rounded-xl border border-slate-300 hover:bg-white text-sm">Ver vacantes</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Vacantes -->
  <section id="jobs" class="bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-center">Trabaja con nosotros</h2>
      <p class="text-center text-slate-600 mt-2">Buscamos talento con mentalidad de producto.</p>
      <div class="mt-8 grid md:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl border border-slate-200 bg-white">
          <div class="text-sm text-slate-500">Producto</div>
          <div class="font-semibold">Product Designer (Mid/Senior)</div>
          <div class="text-sm text-slate-600 mt-1">Remoto — LatAm</div>
          <a href="contacto.html" class="inline-block mt-3 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm">Postular</a>
        </div>
        <div class="p-5 rounded-2xl border border-slate-200 bg-white">
          <div class="text-sm text-slate-500">Ingeniería</div>
          <div class="font-semibold">Full‑stack Developer (Laravel/Vue)</div>
          <div class="text-sm text-slate-600 mt-1">Remoto — LatAm</div>
          <a href="contacto.html" class="inline-block mt-3 px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-50 text-sm">Postular</a>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA final -->
  <section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="relative overflow-hidden rounded-3xl border border-slate-200 p-8 sm:p-12 bg-gradient-to-br from-slate-50 to-brand-50">
        <div class="max-w-2xl">
          <h3 class="text-2xl sm:text-3xl font-extrabold">¿Te gusta lo que hacemos?</h3>
          <p class="mt-2 text-slate-600">Conversemos sobre tu proyecto o únete al equipo.</p>
          <div class="mt-6 flex flex-col sm:flex-row gap-3">
            <a href="contacto.html" class="px-6 py-3 rounded-xl bg-slate-900 text-white hover:bg-slate-800">Hablar con ventas</a>
            <a href="#jobs" class="px-6 py-3 rounded-xl border border-slate-300 hover:bg-slate-50">Ver vacantes</a>
          </div>
        </div>
        <img class="hidden md:block absolute -right-10 -bottom-10 w-72 opacity-80" src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1200&auto=format&fit=crop" alt="Ilustración" />
      </div>
    </div>
  </section>
@endsection