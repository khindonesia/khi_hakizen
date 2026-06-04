@filamentScripts
@livewireScripts
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 429 || status === 409) {
                    preventDefault();
                    if (typeof FilamentNotification !== 'undefined') {
                        new FilamentNotification()
                            .title('Terlalu Banyak Permintaan')
                            .icon('heroicon-o-exclamation-triangle')
                            .iconColor('danger')
                            .send();
                    } else {
                        alert('Terlalu banyak permintaan. Silakan tunggu beberapa saat.');
                    }
                }
            });
        });
    });
</script>
@if(config('wave.dev_bar'))
    @include('theme::partials.dev_bar')
@endif

{{-- @yield('javascript') --}}

@if(setting('site.google_analytics_tracking_id', ''))
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('site.google_analytics_tracking_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ setting("site.google_analytics_tracking_id") }}');
    </script>
@endif

