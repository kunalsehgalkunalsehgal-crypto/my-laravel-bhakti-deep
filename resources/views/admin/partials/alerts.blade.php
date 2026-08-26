@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert error">{{ $errors->first() }}</div>
@endif
