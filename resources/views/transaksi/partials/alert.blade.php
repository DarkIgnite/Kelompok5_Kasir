@if(session('success'))
    <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
        <span>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        <span>{{ session('error') }}</span>
    </div>
@endif
@if(isset($errors) && $errors->any())
    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
