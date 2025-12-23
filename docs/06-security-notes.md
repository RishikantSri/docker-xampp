# Security notes

Local development still needs basic hygiene:

- Never commit `.env` files.
- Never commit certificates/private keys.
- Keep Docker ports bound to localhost unless you explicitly need LAN access.
- Treat database dumps as sensitive.
