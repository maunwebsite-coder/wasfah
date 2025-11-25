@php
    $selectedSubject = old('subject', $defaultSubject ?? 'general');
    $formCopy = $formCopy ?? __('contact.form');
    $fields = $formCopy['fields'] ?? [];
    $subjects = \App\Models\ContactMessage::subjectLabelOptions();
    $responseNotice = $formCopy['response_notice'] ?? null;
    $footerAlignment = $responseNotice ? 'justify-between' : 'justify-end';
@endphp

<input type="hidden" name="source" value="{{ $source ?? 'contact-page' }}">

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ data_get($fields, 'first_name.label', __('contact.form.fields.first_name.label')) }}
        </label>
        <input
            type="text"
            name="first_name"
            value="{{ old('first_name') }}"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('first_name') border-red-500 @enderror"
            placeholder="{{ data_get($fields, 'first_name.placeholder', __('contact.form.fields.first_name.placeholder')) }}"
            required
        >
        @error('first_name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ data_get($fields, 'last_name.label', __('contact.form.fields.last_name.label')) }}
        </label>
        <input
            type="text"
            name="last_name"
            value="{{ old('last_name') }}"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('last_name') border-red-500 @enderror"
            placeholder="{{ data_get($fields, 'last_name.placeholder', __('contact.form.fields.last_name.placeholder')) }}"
            required
        >
        @error('last_name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ data_get($fields, 'email.label', __('contact.form.fields.email.label')) }}
    </label>
    <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('email') border-red-500 @enderror"
        placeholder="{{ data_get($fields, 'email.placeholder', __('contact.form.fields.email.placeholder')) }}"
        required
    >
    @error('email')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ data_get($fields, 'phone.label', __('contact.form.fields.phone.label')) }}
    </label>
    <input
        type="tel"
        name="phone"
        value="{{ old('phone') }}"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('phone') border-red-500 @enderror"
        placeholder="{{ data_get($fields, 'phone.placeholder', __('contact.form.fields.phone.placeholder')) }}"
    >
    @error('phone')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ data_get($fields, 'subject.label', __('contact.form.fields.subject.label')) }}
    </label>
    <select
        name="subject"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('subject') border-red-500 @enderror"
        required
    >
        @foreach ($subjects as $key => $label)
            <option value="{{ $key }}" {{ $selectedSubject === $key ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('subject')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ data_get($fields, 'message.label', __('contact.form.fields.message.label')) }}
    </label>
    <textarea
        name="message"
        rows="5"
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent @error('message') border-red-500 @enderror"
        placeholder="{{ data_get($fields, 'message.placeholder', __('contact.form.fields.message.placeholder')) }}"
        required
    >{{ old('message') }}</textarea>
    @error('message')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="flex flex-wrap items-center gap-4 {{ $footerAlignment }}">
    @if($responseNotice)
        <p class="text-sm text-gray-500">{{ $responseNotice }}</p>
    @endif
    <button
        type="submit"
        class="inline-flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2"
    >
        <i class="fas fa-paper-plane"></i>
        {{ $formCopy['submit'] ?? __('contact.form.submit') }}
    </button>
</div>

