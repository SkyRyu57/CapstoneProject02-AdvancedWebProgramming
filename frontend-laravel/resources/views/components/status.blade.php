@if (session('status'))
    <div class="notice success">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="notice danger">
        @foreach ($errors->all() as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif
