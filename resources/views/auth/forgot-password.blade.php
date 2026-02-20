@component('layouts.theme.login')
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <!-- BEGIN: Info -->
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    <img alt="Facta SaaS" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                    <span class="text-white text-lg ml-3 font-medium">Facta SaaS</span>
                </a>
                <div class="my-auto">
                    <img alt="Recuperar" class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
                        Recupera tu acceso
                    </div>
                    <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-gray-500">
                        Te enviaremos un enlace para restablecer tu contraseña.
                    </div>
                </div>
            </div>
            <!-- END: Info -->
            
            <!-- BEGIN: Form -->
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
                        ¿Olvidaste tu contraseña?
                    </h2>
                    <div class="intro-x mt-2 text-gray-500 xl:hidden text-center">Ingresa tu correo para recuperarla.</div>
                    
                    <div class="intro-x mt-4 text-gray-600 text-sm">
                        No hay problema. Simplemente dinos tu dirección de correo electrónico y te enviaremos un enlace para restablecerla.
                    </div>

                    <!-- Session Status -->
                    @if(session('status'))
                        <div class="intro-x mt-4 text-theme-9 font-medium">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="intro-x mt-4 text-theme-6 font-medium">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('password.email') }}" class="intro-x mt-8">
                        @csrf
                        <input type="email" name="email" class="intro-x login__input form-control py-3 px-4 border-gray-300 block" placeholder="Correo Electrónico" value="{{ old('email') }}" required autofocus>
                        
                        <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                            <button class="btn btn-primary py-3 px-4 w-full xl:w-64 xl:mr-3 align-top">Enviar enlace de recuperación</button>
                        </div>
                        <div class="intro-x mt-4 text-center xl:text-left">
                             <a href="{{ route('login') }}" class="btn btn-outline-secondary py-3 px-4 w-full xl:w-64 mt-2 xl:mt-0 align-top">Volver al Login</a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END: Form -->
        </div>
    </div>
@endcomponent
