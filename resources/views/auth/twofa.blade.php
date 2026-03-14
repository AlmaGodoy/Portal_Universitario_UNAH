@extends('portal_login')

@section('content')
<div class="auth-container">
  <div class="auth-card" style="max-width:520px;">
    <h3 class="mb-4 text-center">Verificación 2FA</h3>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('twofa.verify') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Código (6 dígitos)</label>
        <input name="code" class="form-control" maxlength="6" inputmode="numeric" required autofocus>
      </div>
      <button class="btn btn-primary w-100">Verificar</button>
    </form>
  </div>
</div>
@endsection
