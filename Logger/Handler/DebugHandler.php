<?php

namespace Parceladousa\Payment\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Class DebugHandler
 */
class DebugHandler extends BaseHandler {
  /**
   * Logging level
   *
   * @var int
   */
  protected $loggerType = MonologLogger::DEBUG;

  /**
   * File name
   * @var string
   */
  protected $fileName = '/var/log/parcelado/debug.log';
}
