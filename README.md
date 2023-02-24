# ictuwp-plugin-gc-reply-to-mailaddress
This plugin sets the reply to address for all email sent from the server.

Which address is used?
* If a GC_REPLY_MAIL is set (anywhere), use that address
* else, if a mail_address is set through the SMTP plugin (wpmailsmtp), use that address
* else, use 'admin_email' address


## Current version:
* 1.0.2 - Added extra check for the constant GC_REPLY_MAIL.

