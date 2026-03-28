<script>
    @php
        $toasts = session('toasts', []);

        if ($toasts instanceof \Illuminate\Support\Collection) {
            $toasts = $toasts->all();
        } elseif (! is_array($toasts)) {
            $toasts = [$toasts];
        }
    @endphp

    @foreach($toasts as $toast)
    var options = {
            title: '{{ $toast['title'] }}',
            message: '{{ $toast['message'] }}',
            messageColor: '{{ $toast['messageColor'] }}',
            messageSize: '{{ $toast['messageSize'] }}',
            titleLineHeight: '{{ $toast['titleLineHeight'] }}',
            messageLineHeight: '{{ $toast['messageLineHeight'] }}',
            position: '{{ $toast['position'] }}',
            titleSize: '{{ $toast['titleSize'] }}',
            titleColor: '{{ $toast['titleColor'] }}',
            closeOnClick: '{{ $toast['closeOnClick'] }}',

        };

    var type = '{{  $toast["type"] }}';

    show(type, options);

    @endforeach
    function show(type, options) {
        if (type === 'info'){
            iziToast.info(options);
        }
        else if (type === 'success'){
            iziToast.success(options);
        }
        else if  (type === 'warning'){
            iziToast.warning(options);
        }
        else if (type === 'error'){
            iziToast.error(options);
        } else {
            iziToast.show(options);
        }

    }
</script>

@php(session()->forget('toasts'))
