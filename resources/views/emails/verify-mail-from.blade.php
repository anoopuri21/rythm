<x-mail::message>
# Confirm store sender email

You (or a store admin) asked to use **{{ $pendingAddress }}** as the From address for {{ $storeName }} customer emails (order updates, verification, etc.).

If this is correct, confirm within **24 hours**:

<x-mail::button :url="$confirmUrl">
Confirm sender email
</x-mail::button>

If you did not expect this, ignore this message. The address will not be used until confirmed.

Thanks,<br>
{{ $storeName }}
</x-mail::message>
