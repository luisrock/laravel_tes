<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Erro permanente na análise de acórdãos (sem retry na fila).
 */
class AcordaoAnalysisPermanentException extends RuntimeException {}
