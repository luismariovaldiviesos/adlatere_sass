@component('layouts.theme.login')
    <div class="container sm:px-10">
        <div class="block xl:grid grid-cols-2 gap-4">
            <!-- BEGIN: Login Info -->
            <div class="hidden xl:flex flex-col min-h-screen">
                <a href="" class="-intro-x flex items-center pt-5">
                    <img alt="Facta SaaS" class="w-6" src="{{ asset('dist/images/logo.svg') }}">
                    <span class="text-white text-lg ml-3 font-medium">Facta SaaS</span>
                </a>
                <div class="my-auto">
                    <img alt="Bienvenido" class="-intro-x w-1/2 -mt-16" src="{{ asset('dist/images/illustration.svg') }}">
                    <div class="-intro-x text-white font-medium text-4xl leading-tight mt-10">
                        Bienvenido de nuevo
                        <br>
                        Ingresa a tu cuenta.
                    </div>
                    <div class="-intro-x mt-5 text-lg text-white text-opacity-70 dark:text-gray-500">
                        Gestiona tus ventas y facturación en un solo lugar.
                    </div>
                </div>
            </div>
            <!-- END: Login Info -->
            
            <!-- BEGIN: Login Form -->
            <div class="h-screen xl:h-auto flex py-5 xl:py-0 my-10 xl:my-0">
                <div class="my-auto mx-auto xl:ml-20 bg-white xl:bg-transparent px-5 sm:px-8 py-8 xl:p-0 rounded-md shadow-md xl:shadow-none w-full sm:w-3/4 lg:w-2/4 xl:w-auto">
                    <h2 class="intro-x font-bold text-2xl xl:text-3xl text-center xl:text-left">
                        Iniciar Sesión
                    </h2>
                    <div class="intro-x mt-2 text-gray-500 xl:hidden text-center">Unos pocos clics más para entrar a tu cuenta.</div>
                    
                    <form method="POST" action="{{ route('login') }}" class="intro-x mt-8">
                        @csrf
                        <input type="email" name="email" class="intro-x login__input form-control py-3 px-4 border-gray-300 block" placeholder="Correo Electrónico" value="{{ old('email') }}" required autofocus>
                        @error('email') <div class="text-theme-6 mt-2">{{ $message }}</div> @enderror
                        
                        <input type="password" name="password" class="intro-x login__input form-control py-3 px-4 border-gray-300 block mt-4" placeholder="Contraseña" required>
                        @error('password') <div class="text-theme-6 mt-2">{{ $message }}</div> @enderror
                        
                        <div class="intro-x flex text-gray-700 text-xs sm:text-sm mt-4">
                            <div class="flex items-center mr-auto">
                                <input id="remember-me" name="remember" type="checkbox" class="form-check-input border mr-2">
                                <label class="cursor-pointer select-none" for="remember-me">Recordarme</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-theme-1 hover:underline">¿Olvidaste tu contraseña?</a>
                            @endif
                        </div>
                        <div class="intro-x mt-5 xl:mt-8 text-center xl:text-left">
                            <button class="btn btn-primary py-3 px-4 w-full xl:w-32 xl:mr-3 align-top">Ingresar</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- END: Login Form -->
        </div>
    </div>
@endcomponent
