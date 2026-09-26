@if(session('success'))<div class="member-notice" role="status">{{ session('success') }}</div>@endif
@if(session('status'))<div class="member-notice" role="status">{{ session('status') === 'password-updated' ? 'Password updated.' : (session('status') === 'verification-link-sent' ? 'A new verification link has been sent to your email.' : session('status')) }}</div>@endif
@if($errors->any())<div class="member-notice member-notice--error" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
