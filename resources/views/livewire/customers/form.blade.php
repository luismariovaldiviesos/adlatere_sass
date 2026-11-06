<div class="intro-y col-span-12">
    <div class="intro-y box">
        <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
            <h2 class="font-medium text-base mr-auto">
                {{ $componentName  }} | <span class="font-normal">{{ $action }}</span>
            </h2>
        </div>

        <div class="p-5 ">
            <div class="preview">
                <x-customer-fields :cantones="$cantones" />

                <div class="mt-5">
                    <x-back />

                    <x-save />
                </div>

            </div>
        </div>

    </div>

    <script>
        // Mantiene la sincronización con el teclado en pantalla cuando está activo.
        document.querySelectorAll(".kioskboard").forEach(i => i.addEventListener("change", e => {
            switch (e.currentTarget.id) {
                case 'businame':
                    @this.businame = e.target.value
                    break
                case 'valueidenti':
                    @this.valueidenti = e.target.value
                    break
                case 'address':
                    @this.address = e.target.value
                    break
                case 'email':
                    @this.email = e.target.value
                    break
                case 'phone':
                    @this.phone = e.target.value
                    break
                case 'notes':
                    @this.notes = e.target.value
                    break
            }
        }))
    </script>

</div>
