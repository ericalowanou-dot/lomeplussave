{{-- Profil unifié : même page que les utilisateurs (pages.profile.edit). --}}
@php
    redirect()->route('profile.edit')->send();
@endphp
