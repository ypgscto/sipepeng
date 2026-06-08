@if (session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <x-sipeng.alert type="success">{{ session('success') }}</x-sipeng.alert>
    </div>
@endif

@if (session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <x-sipeng.alert type="error">{{ session('error') }}</x-sipeng.alert>
    </div>
@endif
