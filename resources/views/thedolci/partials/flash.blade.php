@if(session('success'))
    <div class="dolci-alert dolci-alert-success">{{ session('success') }}</div>
@endif

@if(session('warning'))
    <div class="dolci-alert dolci-alert-warning">{{ session('warning') }}</div>
@endif

@if($errors->any())
    <div class="dolci-alert dolci-alert-error">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

