<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pelanggaran aturan bisnis cuti (kuota, bentrok jadwal, hari kerja, dst).
 * Pesannya aman ditampilkan langsung ke pengguna.
 */
class LeaveRuleViolation extends RuntimeException {}
