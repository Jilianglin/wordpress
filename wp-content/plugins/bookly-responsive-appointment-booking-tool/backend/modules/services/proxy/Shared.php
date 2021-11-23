<?php
namespace Bookly\Backend\Modules\Services\Proxy;

use Bookly\Lib;

/**
 * Class Shared
 * @package Bookly\Backend\Modules\Services\Proxy
 *
 * @method static void  duplicateService( int $source_id, int $target_id ) Duplicate service.
 * @method static array prepareServiceColors( array $colors, int $service_id, int $service_type ) Prepare colors for service.
 * @method static array prepareServiceIcons( array $icons ) Prepare service icons.
 * @method static array prepareServiceTypes( array $types ) Prepare service types.
 * @method static array serviceCreated( Lib\Entities\Service $service, array $_post ) Service created.
 * @method static void  serviceDeleted( Lib\Entities\Service $service ) Service deleted.
 */
abstract class Shared extends Lib\Base\Proxy
{

}