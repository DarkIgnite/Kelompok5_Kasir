<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kesalahan bisnis saat checkout (stok kurang, uang kurang, dll).
 * Pesannya aman ditampilkan ke kasir.
 */
class CheckoutException extends RuntimeException
{
}
