@once('alpinejs-3.14.3')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
@endonce
<script>
    function newsletterForm() {
        return {
            name: '',
            email: '',
            loading: false,
            message: '',
            success: false,
            init() {},
            async submit(event) {
                this.loading = true;
                this.message = '';
                const formData = new FormData(event.target);
                try {
                    const res = await fetch(@json(route('newsletter.subscribe')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await res.json();
                    this.success = !!data.success || !!data.already_subscribed;
                    this.message = data.message ?? 'Não foi possível inscrever agora.';
                    if (this.success) {
                        setTimeout(() => window.location.reload(), 1200);
                    }
                } catch (e) {
                    this.success = false;
                    this.message = 'Erro de rede. Tente em instantes.';
                } finally {
                    this.loading = false;
                }
            },
        };
    }

    function newsletterQuickSubscribe() {
        return {
            name: @json(auth()->user()?->name ?? ''),
            email: @json(auth()->user()?->email ?? ''),
            loading: false,
            message: '',
            success: false,
            async subscribe() {
                this.loading = true;
                this.message = '';
                const formData = new FormData();
                formData.append('name', this.name);
                formData.append('email', this.email);
                formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
                try {
                    const res = await fetch(@json(route('newsletter.subscribe')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const data = await res.json();
                    this.success = !!data.success || !!data.already_subscribed;
                    this.message = data.message ?? 'Não foi possível inscrever agora.';
                    if (this.success) {
                        setTimeout(() => window.location.reload(), 1200);
                    }
                } catch (e) {
                    this.success = false;
                    this.message = 'Erro de rede. Tente em instantes.';
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
