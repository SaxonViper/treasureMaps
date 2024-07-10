<!doctype html>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<link rel="stylesheet" href="/css/app.css" />
<link rel="stylesheet" href="/css/style.css" />
<link rel="stylesheet" href="/css/modal.css" />
<link href="https://fonts.googleapis.com/css2?family=Charm:wght@400;700&family=Signika+Negative:wght@300..700&display=swap" rel="stylesheet">
<!-- <link href="https://fonts.googleapis.com/css2?family=Signika+Negative:wght@300..700&display=swap" rel="stylesheet"> -->
<!-- <script src="/js/bootstrap.js"></script> -->
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>

<!-- <script type="text/x-template" id="modal-template">
    <div class="my-modal-backdrop">
        <div class="my-modal">
            <header class="modal-header">
                <slot name="header">
                    This is the default tile!

                    <button
                            type="button"
                            class="btn-close"
                            @click="$emit(closeModal)"
                    >
                        x
                    </button>
                </slot>
            </header>

            <section class="modal-body">
                <slot name="body">
                    I'm the default body!
                </slot>
            </section>

            <footer class="modal-footer">
                <slot name="footer">
                    I'm the default footer!
                </slot>
            </footer>
        </div>
    </div>
</script> -->

<div id="app">
    <!-- <login-component></login-component> -->
    <example-component></example-component>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="/js/app.js"></script>
