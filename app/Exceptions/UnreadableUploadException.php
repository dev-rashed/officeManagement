<?php

namespace App\Exceptions;

/**
 * The uploaded file passed validation but its temporary copy could not be read
 * back off disk.
 *
 * Rare, and always environmental — a temp directory that has been cleared, a
 * path the process cannot resolve, a full disk. It is not the user's mistake,
 * but they are the one who has to retry, so HandlesImageUploads turns this into
 * a field-level validation message rather than a 500.
 */
class UnreadableUploadException extends \RuntimeException
{
}
