<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\AccessType
 *
 * @property int $id
 * @property string $application_name
 * @property string $applicationKey
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType whereApplicationKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType whereApplicationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccessType whereUpdatedAt($value)
 */
	class AccessType extends \Eloquent {}
}

namespace App{
/**
 * App\Brand
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereUpdatedAt($value)
 */
	class Brand extends \Eloquent {}
}

namespace App{
/**
 * App\Carton
 *
 * @property int $id
 * @property int $wave_id
 * @property int $order_id
 * @property int $total_pieces
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $transferNum
 * @property string|null $transferStatus
 * @property string|null $waveNumber
 * @property string|null $businessName
 * @property string|null $area
 * @property int|null $orderNumber
 * @property string|null $barcode
 * @property int|null $route
 * @property string|null $route_name
 * @property int|null $store
 * @property string|null $store_name
 * @property string|null $labelDetail
 * @property int|null $shipment
 * @property int $pendingConfirmation
 * @property int $audited_by
 * @property string|null $audit_init
 * @property string|null $audit_end
 * @property int|null $authorized_by
 * @property-read \App\User $auditedBy
 * @property-read \App\User|null $authorizedBy
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CartonLine[] $cartonLines
 * @property-read int|null $carton_lines_count
 * @property-read \App\Order $order
 * @property-read \App\Wave $wave
 * @method static \Illuminate\Database\Eloquent\Builder|Carton newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Carton newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Carton query()
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereAuditEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereAuditInit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereAuditedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereAuthorizedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereBusinessName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereLabelDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton wherePendingConfirmation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereRouteName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereShipment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereStore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereStoreName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereTotalPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereTransferNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereTransferStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Carton whereWaveNumber($value)
 */
	class Carton extends \Eloquent {}
}

namespace App{
/**
 * App\CartonLine
 *
 * @property int $id
 * @property int $carton_id
 * @property int $line_id
 * @property int $prepacks
 * @property int $pieces
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $sku
 * @property string|null $style
 * @property int $prepacks_aud
 * @property int $pieces_aud
 * @property-read \App\Carton $carton
 * @property-read \App\Line $line
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereCartonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine wherePieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine wherePiecesAud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine wherePrepacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine wherePrepacksAud($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartonLine whereUpdatedAt($value)
 */
	class CartonLine extends \Eloquent {}
}

namespace App{
/**
 * App\Color
 *
 * @property int $id
 * @property string $name
 * @property string $hexadecimal_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Color newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Color newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Color query()
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereHexadecimalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Color whereUpdatedAt($value)
 */
	class Color extends \Eloquent {}
}

namespace App{
/**
 * App\Department
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $ranking
 * @property int $division_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $jda_name
 * @property int|null $jda_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductClasses[] $classes
 * @property-read int|null $classes_count
 * @property-read \App\Division $division
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 */
	class Department extends \Eloquent {}
}

namespace App{
/**
 * App\Devtrf
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\DevtrfContent[] $contents
 * @property-read int|null $contents_count
 * @method static \Illuminate\Database\Eloquent\Builder|Devtrf newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Devtrf newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Devtrf query()
 */
	class Devtrf extends \Eloquent {}
}

namespace App{
/**
 * App\DevtrfContent
 *
 * @property int $id
 * @property int $devtrf_id
 * @property string $sku
 * @property int $variation_id
 * @property int $pieces
 * @property int $prepacks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Devtrf $devtrf
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent query()
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent whereDevtrfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent wherePieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent wherePrepacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DevtrfContent whereVariationId($value)
 */
	class DevtrfContent extends \Eloquent {}
}

namespace App{
/**
 * App\Division
 *
 * @property int $id
 * @property string $name
 * @property string|null $processed_in
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $jda_id
 * @property string|null $jda_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Department[] $departments
 * @property-read int|null $departments_count
 * @method static \Illuminate\Database\Eloquent\Builder|Division newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Division newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Division query()
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereProcessedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Division whereUpdatedAt($value)
 */
	class Division extends \Eloquent {}
}

namespace App{
/**
 * App\Image
 *
 * @property-read \App\Color $color
 * @property-read \App\Style $style
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 */
	class Image extends \Eloquent {}
}

namespace App{
/**
 * App\Line
 *
 * @property int $id
 * @property int $sku
 * @property int $pieces
 * @property int $prepacks
 * @property int $active
 * @property int $order_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $variation_id
 * @property int $style_id
 * @property int|null $wave_id
 * @property int $complete
 * @property int $pieces_in_carton
 * @property int $prepacks_in_carton
 * @property int $ppk
 * @property int $status
 * @property int $division_id
 * @property int|null $expected_pieces
 * @property int|null $department_id
 * @property int|null $ppksaalma
 * @property int $updated_by
 * @property int $priority
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CartonLine[] $cartonLine
 * @property-read int|null $carton_line_count
 * @property-read \App\Department|null $department
 * @property-read \App\Division $divisionModel
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Log[] $logs
 * @property-read int|null $logs_count
 * @property-read \App\Order $order
 * @property-read \App\Style $style
 * @method static \Illuminate\Database\Eloquent\Builder|Line newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Line newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Line query()
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereExpectedPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePiecesInCarton($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePpk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePpksaalma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePrepacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePrepacksInCarton($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereStyleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereVariationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Line whereWaveId($value)
 */
	class Line extends \Eloquent {}
}

namespace App{
/**
 * App\Log
 *
 * @property int $id
 * @property string $message
 * @property int $loggable_id
 * @property string $loggable_type
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $loggable
 * @method static \Illuminate\Database\Eloquent\Builder|Log newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Log query()
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereLoggableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereLoggableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Log whereUserId($value)
 */
	class Log extends \Eloquent {}
}

namespace App{
/**
 * App\LogPrintCarton
 *
 * @property int $id
 * @property string $barcode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton query()
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton whereBarcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LogPrintCarton whereUpdatedAt($value)
 */
	class LogPrintCarton extends \Eloquent {}
}

namespace App{
/**
 * App\Order
 *
 * @property int $id
 * @property int $store_id
 * @property string|null $slots
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $order_group_id
 * @property int|null $merc_id
 * @property string|null $routeDescription
 * @property int $routePriority
 * @property string|null $storeDescription
 * @property int $storePriority
 * @property int $storeNumber
 * @property int $routeNumber
 * @property int $status
 * @property int $storePosition
 * @property int|null $allocation
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Carton[] $cartons
 * @property-read int|null $cartons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $lines
 * @property-read int|null $lines_count
 * @property-read \App\OrderGroup|null $ordergroup
 * @property-read \App\Store $store
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAllocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereMercId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRouteDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRouteNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereRoutePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStoreDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStoreNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStorePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStorePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 */
	class Order extends \Eloquent {}
}

namespace App{
/**
 * App\OrderGroup
 *
 * @property int $id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $reference
 * @property int|null $local
 * @property int|null $allocation
 * @property int|null $transferencia
 * @property int $statusMerged
 * @property int|null $solicitudId
 * @property int|null $claveOS
 * @property int|null $allocationgroup
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $lines
 * @property-read int|null $lines_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Log[] $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Order[] $orders
 * @property-read int|null $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereAllocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereAllocationgroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereClaveOS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereLocal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereSolicitudId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereStatusMerged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereTransferencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderGroup whereUpdatedAt($value)
 */
	class OrderGroup extends \Eloquent {}
}

namespace App{
/**
 * App\PalletContent
 *
 * @property int $id
 * @property string $folio_mov
 * @property int $sku
 * @property int $cantidad
 * @property int $cajas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $variation_id
 * @property int $department_id
 * @property int $wave_id
 * @property int $pallet_id
 * @property int $style_id
 * @property string $style
 * @property-read \App\Department $department
 * @property-read \App\Pallets $pallets
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent query()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereCajas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereCantidad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereFolioMov($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent wherePalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereStyleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereVariationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletContent whereWaveId($value)
 */
	class PalletContent extends \Eloquent {}
}

namespace App{
/**
 * App\PalletMovement
 *
 * @property int $id
 * @property string $session
 * @property int $wave_id
 * @property int $pallet_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $from_zone
 * @property string $to_zone
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereFromZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement wherePalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereToZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PalletMovement whereWaveId($value)
 */
	class PalletMovement extends \Eloquent {}
}

namespace App{
/**
 * App\Pallets
 *
 * @property int $id
 * @property string $fecha_mov
 * @property string|null $lpn_transportador
 * @property string $almacen_dest
 * @property string $ubicacion_dest
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $wave_id
 * @property int $enqueue
 * @property int $zone_id
 * @property string|null $assignated_by
 * @property int|null $boxes
 * @property string|null $inducted_by
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PalletContent[] $palletsSku
 * @property-read int|null $pallets_sku_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PalletContent[] $ranking
 * @property-read int|null $ranking_count
 * @property-read \App\Wave $wave
 * @property-read \App\Zone $zone
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereAlmacenDest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereAssignatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereBoxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereEnqueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereFechaMov($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereInductedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereLpnTransportador($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereUbicacionDest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pallets whereZoneId($value)
 */
	class Pallets extends \Eloquent {}
}

namespace App{
/**
 * App\Permissions
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permissions whereUpdatedAt($value)
 */
	class Permissions extends \Eloquent {}
}

namespace App{
/**
 * App\PermissionsHasAccess
 *
 * @property int $id_permission
 * @property int $id_access
 * @property string $access
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess query()
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess whereAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess whereIdAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess whereIdPermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PermissionsHasAccess whereUpdatedAt($value)
 */
	class PermissionsHasAccess extends \Eloquent {}
}

namespace App{
/**
 * App\PickingOrders
 *
 * @property int $id
 * @property int $wave_id
 * @property string $sku
 * @property int $pieces
 * @property int $boxes
 * @property int $department_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $real_pieces
 * @property int|null $real_boxes
 * @property string|null $location
 * @property string $status
 * @property-read \App\Department $department
 * @property-read \App\Wave $wave
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders query()
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereBoxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders wherePieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereRealBoxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereRealPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PickingOrders whereWaveId($value)
 */
	class PickingOrders extends \Eloquent {}
}

namespace App{
/**
 * App\Priority
 *
 * @property int $id
 * @property string $label
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $jda_id
 * @property string|null $jda_name
 * @method static \Illuminate\Database\Eloquent\Builder|Priority newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Priority newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Priority query()
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Priority whereUpdatedAt($value)
 */
	class Priority extends \Eloquent {}
}

namespace App{
/**
 * App\ProductClasses
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $label
 * @property int $department_id
 * @property-read \App\Department $department
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductType[] $types
 * @property-read int|null $types_count
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClasses whereUpdatedAt($value)
 */
	class ProductClasses extends \Eloquent {}
}

namespace App{
/**
 * App\ProductClassification
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $label
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductClassification whereUpdatedAt($value)
 */
	class ProductClassification extends \Eloquent {}
}

namespace App{
/**
 * App\ProductColor
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $color_dictionary
 * @property string|null $hexadecimal_color
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereColorDictionary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereHexadecimalColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductColor whereUpdatedAt($value)
 */
	class ProductColor extends \Eloquent {}
}

namespace App{
/**
 * App\ProductFabric
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFabric whereUpdatedAt($value)
 */
	class ProductFabric extends \Eloquent {}
}

namespace App{
/**
 * App\ProductFamily
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $label
 * @property int|null $ranking
 * @property int|null $classification_id
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFamily whereUpdatedAt($value)
 */
	class ProductFamily extends \Eloquent {}
}

namespace App{
/**
 * App\ProductFit
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductFit whereUpdatedAt($value)
 */
	class ProductFit extends \Eloquent {}
}

namespace App{
/**
 * App\ProductPriority
 *
 * @property int $id
 * @property string $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPriority whereUpdatedAt($value)
 */
	class ProductPriority extends \Eloquent {}
}

namespace App{
/**
 * App\ProductProvider
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $label
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProvider whereUpdatedAt($value)
 */
	class ProductProvider extends \Eloquent {}
}

namespace App{
/**
 * App\ProductSize
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $label
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductSize whereUpdatedAt($value)
 */
	class ProductSize extends \Eloquent {}
}

namespace App{
/**
 * App\ProductType
 *
 * @property int $id
 * @property int $jdaId
 * @property string $jdaName
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $label
 * @property int $clasz_id
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereClaszId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereJdaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereJdaName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductType whereUpdatedAt($value)
 */
	class ProductType extends \Eloquent {}
}

namespace App{
/**
 * App\ReasonCancel
 *
 * @property int $id
 * @property string $reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Wave $wave
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReasonCancel whereUpdatedAt($value)
 */
	class ReasonCancel extends \Eloquent {}
}

namespace App{
/**
 * App\ResetPasswordUser
 *
 * @property string $email
 * @property string $token
 * @property string|null $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|ResetPasswordUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResetPasswordUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResetPasswordUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResetPasswordUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResetPasswordUser whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResetPasswordUser whereToken($value)
 */
	class ResetPasswordUser extends \Eloquent {}
}

namespace App{
/**
 * App\Role
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $description
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 */
	class Role extends \Eloquent {}
}

namespace App{
/**
 * App\Route
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $color
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Store[] $stores
 * @property-read int|null $stores_count
 * @method static \Illuminate\Database\Eloquent\Builder|Route newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Route newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Route query()
 * @method static \Illuminate\Database\Eloquent\Builder|Route whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Route whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Route whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Route whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Route whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Route whereUpdatedAt($value)
 */
	class Route extends \Eloquent {}
}

namespace App{
/**
 * App\Size
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Size newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Size newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Size query()
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereUpdatedAt($value)
 */
	class Size extends \Eloquent {}
}

namespace App{
/**
 * App\Store
 *
 * @property int $id
 * @property int $number
 * @property int $ranking
 * @property string $name
 * @property int|null $sorter_ranking
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $route_id
 * @property int $pbl_ranking
 * @property int $position
 * @property int $status
 * @property int $rmsId
 * @property string $rmsName
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Carton[] $cartons
 * @property-read int|null $cartons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Order[] $orders
 * @property-read int|null $orders_count
 * @property-read \App\Route $route
 * @method static \Illuminate\Database\Eloquent\Builder|Store newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Store newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Store query()
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store wherePblRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereRmsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereRmsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereRouteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereSorterRanking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Store whereUpdatedAt($value)
 */
	class Store extends \Eloquent {}
}

namespace App{
/**
 * App\StoreDepartment
 *
 * @property int $id
 * @property int $store_id
 * @property string $storeNumber
 * @property int|null $department_id
 * @property string|null $departmentNumber
 * @property string|null $block_until
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property string $user_name
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment newQuery()
 * @method static \Illuminate\Database\Query\Builder|StoreDepartment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment query()
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereBlockUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereDepartmentNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereStoreNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StoreDepartment whereUserName($value)
 * @method static \Illuminate\Database\Query\Builder|StoreDepartment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|StoreDepartment withoutTrashed()
 */
	class StoreDepartment extends \Eloquent {}
}

namespace App{
/**
 * App\Style
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $deleted
 * @property string $style
 * @property int|null $jdaDivision
 * @property int|null $jdaDepartment
 * @property int|null $jdaClass
 * @property int|null $jdaType
 * @property int|null $jdaClassification
 * @property int|null $jdaFamily
 * @property int|null $jdaBrand
 * @property int|null $jdaProvider
 * @property int|null $division_id
 * @property int|null $department_id
 * @property int|null $class_id
 * @property int|null $type_id
 * @property int|null $classification_id
 * @property int|null $family_id
 * @property int|null $brand_id
 * @property int|null $provider_id
 * @property string|null $description
 * @property string|null $satCode
 * @property string|null $satUnit
 * @property string|null $publicPrice
 * @property string|null $originalPrice
 * @property string|null $regularPrice
 * @property string|null $publicUsdPrice
 * @property string|null $publicQtzPrice
 * @property string|null $cost
 * @property int $active
 * @property int $international
 * @property-read \App\ProductClassification|null $classification
 * @property-read \App\Department|null $department
 * @property-read \App\Division|null $division
 * @property-read \App\ProductFamily|null $family
 * @method static \Illuminate\Database\Eloquent\Builder|Style newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Style newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Style query()
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereClassId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereClassificationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereInternational($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaFamily($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereJdaType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereOriginalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style wherePublicPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style wherePublicQtzPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style wherePublicUsdPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereRegularPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereSatCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereSatUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereStyle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereUpdatedAt($value)
 */
	class Style extends \Eloquent {}
}

namespace App{
/**
 * App\Team
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $id_administrator
 * @property int $id_department
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User|null $administrator
 * @property-read \App\Department $department
 * @method static \Illuminate\Database\Eloquent\Builder|Team newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Team query()
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereIdAdministrator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereIdDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Team whereUpdatedAt($value)
 */
	class Team extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $active
 * @property int|null $extension
 * @property string|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Log[] $logs
 * @property-read int|null $logs_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Permission\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @property-read \App\Store $store
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @property-read int|null $tokens_count
 * @property-read \App\UserTeam $userTeam
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App{
/**
 * App\UserReport
 *
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string|null $subscrited_to
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $aws
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereAws($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereSubscritedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserReport whereUpdatedAt($value)
 */
	class UserReport extends \Eloquent {}
}

namespace App{
/**
 * App\UserTeam
 *
 * @property int $id
 * @property int $id_team
 * @property int $id_operator
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $operator
 * @property-read \App\Team $team
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam whereIdOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam whereIdTeam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserTeam whereUpdatedAt($value)
 */
	class UserTeam extends \Eloquent {}
}

namespace App{
/**
 * App\Variation
 *
 * @property int $id
 * @property int|null $product_id
 * @property int $sku
 * @property string|null $name
 * @property string|null $color_id
 * @property int|null $stock
 * @property int|null $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $ppk
 * @property int|null $ppc
 * @property int|null $style_id
 * @property int|null $jdaSize
 * @property int|null $size_id
 * @property int|null $jdaColor
 * @property string|null $jdaPriority
 * @property int|null $priority_id
 * @property string|null $weight
 * @property int|null $division_id
 * @property int|null $department_id
 * @property-read \App\Color|null $color
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $lines
 * @property-read int|null $lines_count
 * @property-read \App\Style|null $style
 * @method static \Illuminate\Database\Eloquent\Builder|Variation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereColorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereJdaColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereJdaPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereJdaSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation wherePpc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation wherePpk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation wherePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereSizeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereStyleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variation whereWeight($value)
 */
	class Variation extends \Eloquent {}
}

namespace App{
/**
 * App\Wave
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $area
 * @property int|null $area_id
 * @property int $pieces
 * @property int $complete
 * @property string|null $business_rules
 * @property int $order_group_id
 * @property string|null $wave_ref
 * @property int $status
 * @property int $sorted_pieces
 * @property int $picked_pieces
 * @property int $verified_stock
 * @property string|null $description
 * @property int $total_sku
 * @property int|null $planned_pieces
 * @property string|null $induction_start
 * @property int $stock_pieces
 * @property int $verify_slots
 * @property string|null $order_slots
 * @property int $canceled_by_user_id
 * @property int $reason_cancel_wave_id
 * @property int $available_skus
 * @property int $priority_id
 * @property int|null $picked_boxes
 * @property int|null $sorted_boxes
 * @property int|null $sorted_prepacks
 * @property int|null $prepacks
 * @property string|null $picking_end
 * @property string|null $picking_start
 * @property string|null $induction_end
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Carton[] $cartons
 * @property-read int|null $cartons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $lines
 * @property-read int|null $lines_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PalletContent[] $linesDetail
 * @property-read int|null $lines_detail_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $linesProgress
 * @property-read int|null $lines_progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Line[] $linesSkuSeeder
 * @property-read int|null $lines_sku_seeder_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Log[] $logs
 * @property-read int|null $logs_count
 * @property-read \App\OrderGroup $ordergroup
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Pallets[] $palletDetail
 * @property-read int|null $pallet_detail_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Pallets[] $pallets
 * @property-read int|null $pallets_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PalletContent[] $pickedSkus
 * @property-read int|null $picked_skus_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ReasonCancel[] $reasons
 * @property-read int|null $reasons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Pallets[] $zones
 * @property-read int|null $zones_count
 * @method static \Illuminate\Database\Eloquent\Builder|Wave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wave query()
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereArea($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereAreaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereAvailableSkus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereBusinessRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereCanceledByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereComplete($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereInductionEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereInductionStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereOrderGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereOrderSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePickedBoxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePickedPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePickingEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePickingStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePlannedPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePrepacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave wherePriorityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereReasonCancelWaveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereSortedBoxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereSortedPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereSortedPrepacks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereStockPieces($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereTotalSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereVerifiedStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereVerifySlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wave whereWaveRef($value)
 */
	class Wave extends \Eloquent {}
}

namespace App{
/**
 * App\WavePriority
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority query()
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WavePriority whereUpdatedAt($value)
 */
	class WavePriority extends \Eloquent {}
}

namespace App{
/**
 * App\Zone
 *
 * @property int $id
 * @property int $zone_type_id
 * @property string|null $description
 * @property string $code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $pallet_id
 * @property-read \App\Pallets $pallet
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Pallets[] $pallets
 * @property-read int|null $pallets_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Pallets[] $palletsWithContent
 * @property-read int|null $pallets_with_content_count
 * @property-read \App\ZoneType $zonetype
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone query()
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone wherePalletId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Zone whereZoneTypeId($value)
 */
	class Zone extends \Eloquent {}
}

namespace App{
/**
 * App\ZoneType
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType query()
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ZoneType whereUpdatedAt($value)
 */
	class ZoneType extends \Eloquent {}
}

