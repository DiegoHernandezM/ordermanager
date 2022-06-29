<?php

namespace App\Managers;

use App\Log as Logger;
use App\OrderGroup;
use App\Repositories\OrderGroupRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserReportsRepository;
use App\Repositories\LineRepository;
use App\UserReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SendMailsManager
{
    protected $ogRepository;
    protected $reportRepository;
    protected $userReportsRepository;
    protected $lineRepository;

    public function __construct()
    {
        $this->ogRepository = new OrderGroupRepository();
        $this->reportRepository = new ReportRepository();
        $this->userReportsRepository = new UserReportsRepository();
        $this->lineRepository = new LineRepository();
    }

    /**
     * Se envian correos a mails registrados para OrderGroupReports
     * @return bool
     */
    public function sendMailOrderGroup()
    {
        try {
            setlocale(LC_TIME, 'es_ES');
            $today = new Carbon();
            $dia = $today->formatLocalized('%A');
            $orderGroupBis = OrderGroup::where('reference', 'like', '%.BIS')->orderBy('id', 'desc')->first();
            $orderGroupJda = OrderGroup::where('reference', 'not like', '%.BIS')->where('reference', 'not like', 'FALT.%')->orderBy('id', 'desc')->first();
            $reportBis = DB::table('lines')
                ->selectRaw('sum(pieces) pieces')
                ->selectRaw('division_id')
                ->join('orders', 'orders.id', '=', 'lines.order_id')
                ->where('orders.order_group_id', $orderGroupBis->id)
                ->where('lines.division_id', '!=', 9)
                ->where('lines.division_id', '!=', 10)
                ->groupBy('lines.division_id')
                ->get();
            $reportJda = DB::table('lines')
                ->selectRaw('sum(pieces) pieces')
                ->selectRaw('division_id')
                ->join('orders', 'orders.id', '=', 'lines.order_id')
                ->where('orders.order_group_id', $orderGroupJda->id)
                ->where('lines.division_id', '!=', 9)
                ->where('lines.division_id', '!=', 10)
                ->groupBy('lines.division_id')
                ->get();
            $cellsJda = ['C2', 'C3', 'C4', 'C5', 'C6', 'C7'];
            $cellsBis = ['D2', 'D3', 'D4', 'D5', 'D6', 'D7'];
            if ($today->dayOfWeek == Carbon::THURSDAY)
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('Plantilla de control Jueves.xlsx');
            else
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('Plantilla de control Domingo.xlsx');
            $sheet = $spreadsheet->getActiveSheet();

            foreach ($reportJda as $jda) {
                $sheet->setCellValue($cellsJda[$jda->division_id - 1], $jda->pieces);
            }

            foreach ($reportBis as $bis) {
                $sheet->setCellValue($cellsBis[$bis->division_id - 1], $bis->pieces);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Html($spreadsheet);
            $hdr = $writer->generateHTMLHeader();
            $sty = "<style>
            table, span {
              font-family: arial, sans-serif;
              border-collapse: collapse;
              width: 100%;
            }

            td, th {
              border: 1px solid #dddddd;
              text-align: left;
              padding: 8px;
            }

            .column1, .column2, .column3, .column4, .column5, .column6 {
                text-align: right;
            }

            .row0, .row7 {
                font-weight: bold;
            }

            .row0 {
                background-color:darkorange;
            }

            tr:nth-child(even) {
              background-color: #f3eee2;
            }
            </style>";
            $dat = $writer->generateSheetData() . "\n\n";
            $ftr = $writer->generateHTMLFooter();
            $hdr = preg_replace('@</head>@', "$sty \n</head>", $hdr);
            $msg1 = "<span>Buenos días Ivonne, se anexa la tabla de control del día $dia. </span><br /><br />";
            $msg2 = "<br /><br /><span>Saludos!</span>";
            $html = $hdr . $msg1 . $dat . $msg2 . $ftr;
            $this->buildMail(['mtz_wg@hotmail.com'], 'Plantilla de control', null, $html, ['mtzwgabr@gmail.com']);
            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'Ordergroups mail',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }

    /**
     * Se envia correo a mails registrados para ReportWaves
     * @return bool
     */
    public function sendMailReportWaves()
    {
        try {
            $request = (object) [
                'initDate' => Carbon::parse(Carbon::now()->subDays(1))->format('Y-m-d'),
                'endDate'  => Carbon::parse(Carbon::now())->format('Y-m-d'),
            ];

            $reportWaveFile = $this->reportRepository->getPlannedWaves($request, true);

            if (!$reportWaveFile === false) {
                $sendTo = [];
                $users = $this->getUsersReport();
                foreach ($users as $user) {
                    $subscrites = json_decode($user->subscrited_to);
                    foreach ($subscrites as $subscrite) {
                        if ($subscrite === UserReport::RW) {
                            $sendTo[] = $user->email;
                        }
                    }
                }
                $this->buildMail($sendTo, 'Reporte olas', $reportWaveFile, 'Se adjuntan los indicadores de olas. Saludos.');
                unlink(public_path('files/' . $reportWaveFile . '.xlsx'));
            }

            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'System',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }

    /**
     * @return UserReportsRepository[]|\Exception|\Illuminate\Database\Eloquent\Collection
     */
    private function getUsersReport()
    {
        $users = $this->userReportsRepository->getUsersMails();
        return $users;
    }

    /**
     * Se envia correo con los ppk y skus
     * @return bool
     */
    public function sendMailReportPpkCorrections()
    {
        try {
            $reportPpk = $this->lineRepository->getPpkCorrections();

            if (!$reportPpk === false) {
                $sendTo = ['amartinezw@agarcia.com.mx'];
                $this->buildMail($sendTo, 'Reporte Ppk Correciones', $reportPpk, 'Se adjunta el archivo que contiene los sku. Saludos.');
                unlink(public_path('files/' . $reportPpk . '.xlsx'));
            }
            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'sendMailReportPpkCorrections',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }

    /**
     * Correo de registro de usuario
     * @return bool
     */
    public function newUserMail($name, $email, $password)
    {
        try {
            $welcome = "<span>Hola $name!<br /><br /> Tienes acceso al sistema OMS (Order Manager System) por medio de cualquiera de las siguientes ligas:</span><br /><br />";
            $links = "http://172.16.38.2:5000 OMS Version 1 <br />http://172.16.38.2:3000 OMS Version 2 <br /><br />";
            $cred = "Tu usuario es: $email <br />Contraseña: $password <br /><br />";
            $footer = "Cualquier duda o comentario por favor escríbenos a este correo: <br /><br />soporteoms@agarcia.com.mx<br /><br />Saludos.";
            $html = $welcome . $links . $cred . $footer;
            $sendTo = [$email];
            $this->buildMail($sendTo, 'Acceso OMS', null, $html, ['dhernandezm@agarcia.com.mx', 'aicarrillo@agarcia.com.mx', 'amartinezw@agarcia.com.mx']);
            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'newUserMail',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }

    /**
     * Correo de registro de usuario
     * @return bool
     */
    public function sendTokenResetPassword($name, $email, $token)
    {
        try {
            $welcome = "<span>Hola $name!<br /><br /> Recientemente se solicitó restablecer tu contraseña de OMS (Order Manager System):</span><br /><br />";
            $links = "Para restablecer tu contraseña da click en la siguiente liga: <br /><a href='http://harrier.agarcia.com.mx/validatetoken/$token' target='_blank'>Restablecer contraseña</a> <br /><br />";
            $cred = "Si tú no realizaste ésta solicitud puedes ignorar el mensaje sin problema. <br /><br />";
            $footer = "Cualquier duda o comentario por favor escríbenos a este correo: <br /><br />soporteoms@agarcia.com.mx<br /><br />Saludos.";
            $html = $welcome . $links . $cred . $footer;
            $sendTo = [$email];
            $this->buildMail($sendTo, 'OMS: Restauración de contraseña', null, $html);
            return true;
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'sendTokenResetPassword',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }

    /**
     * Correo de validación de ordenes de surtido
     * @return bool
     */
    public function sendDuplicatesInOrderGroups($ordergroup)
    {
        try {
            setlocale(LC_TIME, 'es_ES');
            $like = str_ends_with($ordergroup->reference, '.BIS') ? 'like' : 'not like';
            $ordergroup2 = OrderGroup::where('reference', $like, '%.BIS')->where('id', '!=', $ordergroup->id)->orderBy('id', 'desc')->first();
            // $orderGroupsJda = OrderGroup::where('reference', 'not like', '%.BIS')->where('reference', 'not like', 'FALT.%')->orderBy('id', 'desc')->limit(2)->get();
            $duplicates = DB::select("call find_duplicates_in_ordergroups(" . $ordergroup->id . ", " . $ordergroup2->id . ")");
            $totalSurtido1 = array_sum(array_column($duplicates, 'piezas'));
            $totalSurtido2 = array_sum(array_column($duplicates, 'piezas2'));
            $totalDiferencias = array_sum(array_column($duplicates, 'diferencia'));
            if (array_sum(array_column($duplicates, 'piezas')) > 1000) {
                $duplicates = collect($duplicates);
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $row = 2;

                $sheet->setCellValue('A1', 'Grupo');
                $sheet->setCellValue('B1', 'ID Linea');
                $sheet->setCellValue('C1', 'Ola');
                $sheet->setCellValue('D1', 'Depto.');
                $sheet->setCellValue('E1', 'Estilo');
                $sheet->setCellValue('F1', 'SKU');
                $sheet->setCellValue('G1', 'ID Tienda');
                $sheet->setCellValue('H1', 'Destino');
                $sheet->setCellValue('I1', 'Piezas');
                $sheet->setCellValue('J1', 'Surtidas');
                $sheet->setCellValue('K1', 'Grupo 2');
                $sheet->setCellValue('L1', 'ID Linea 2');
                $sheet->setCellValue('M1', 'Ola 2');
                $sheet->setCellValue('N1', 'Sku 2');
                $sheet->setCellValue('O1', 'ID Tienda 2');
                $sheet->setCellValue('P1', 'Destino 2');
                $sheet->setCellValue('Q1', 'Piezas 2');
                $sheet->setCellValue('R1', 'Surtidas 2');
                $sheet->setCellValue('S1', 'Diferencia');
                $data = $duplicates->map(fn ($l) => collect($l)->flatten())->toArray();
                $sheet->fromArray($data, NULL, 'A2', true);
                $s1 = new Carbon($ordergroup->created_at);
                $s1 = $s1->formatLocalized('%A');
                $s2 = new Carbon($ordergroup2->created_at);
                $s2 = $s2->formatLocalized('%A');
                $row = count($data) + 1;
                $sheet->getStyle('A1:Q1')->getFont()->setBold(true);
                $sheet->getStyle('A2:S' . $row)->getNumberFormat()->setFormatCode('#');
                $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
                $sheet->setCellValue('T3', "Total Surtido $s1");
                $sheet->setCellValue('U3', $totalSurtido1);
                $spreadsheet->getActiveSheet()->getStyle("T3")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('919FFF');
                $sheet->setCellValue('T4', "Total Surtido $s2");
                $sheet->setCellValue('U4', $totalSurtido2);
                $spreadsheet->getActiveSheet()->getStyle("T4")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8EB5C6');
                $sheet->setCellValue('T5', 'Total diferencias');
                $sheet->setCellValue('U5', $totalDiferencias);
                $spreadsheet->getActiveSheet()->getStyle("T5")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB182');
                $sheet->getStyle('T3:T5')->getFont()->setBold(true);
                $spreadsheet->getActiveSheet()->getStyle("A1:J$row")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('919FFF');
                $spreadsheet->getActiveSheet()->getStyle("K1:S$row")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('8EB5C6');
                $spreadsheet->getActiveSheet()->getStyle("S1:S$row")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFB182');

                $sheet->getColumnDimension('B')->setVisible(false);
                $sheet->getColumnDimension('G')->setVisible(false);
                $sheet->getColumnDimension('L')->setVisible(false);
                $sheet->getColumnDimension('O')->setVisible(false);

                $file = uniqid();
                $writer = new Xlsx($spreadsheet);
                $writer->save(public_path('files/' . $file . '.xlsx'));
                $welcome = "<span>Buenos días!<br /><br /> Se les envía un archivo con distribuciones duplicadas que se encontraron en las órdenes de surtido recientes (" . $ordergroup->reference . " y " . $ordergroup2->reference . "</span><br /><br />";
                $links = "Favor de responder a éste correo si es que se necesita remover o ajustar la distribución para los surtidos. <br />";
                $footer = "Quedo a la orden. <br /><br />Saludos.";
                $html = $welcome . $links . $footer;
                $sendTo = ['amartinezw@agarcia.com.mx'];
                $this->buildMail($sendTo, 'OMS: Duplicidad de distribuciones detectada.', $file, $html);
                unlink(public_path('files/' . $file . '.xlsx'));
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $logData = [
                'message'       => 'Linea ' . $e->getLine() . ' ' . $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'duplicatesReport',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }

    /**
     * Construccion de mail
     * @param $to
     * @param $nameTo
     * @param $subject
     * @param $fileName
     * @param $body
     */
    private function buildMail($to, $subject, $fileName, $body, $bcc = null)
    {
        try {
            Mail::send([], [], function (Message $message) use ($to, $subject, $fileName, $body, $bcc) {
                $message
                    ->to($to)
                    ->from('amartinezw@agarcia.com.mx', 'Abraham Martinez')
                    ->subject($subject)
                    ->setBody($body, 'text/html');
                if ($bcc)
                    $message->bcc($bcc);
                if ($fileName)
                    $message->attach(public_path('files/' . $fileName . '.xlsx'));
            });
        } catch (\Exception $e) {
            $logData = [
                'message'       => $e->getMessage(),
                'loggable_id'   => Logger::LOG_SYSTEM,
                'loggable_type' => 'System',
                'user_id'       => 1,
            ];
            $log = new Logger();
            $log->create($logData);
        }
    }
}
