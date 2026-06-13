<?php
$count = DB::table('security_logs')->count();
echo "Total logs: $count\n";
$rows = DB::table('security_logs')
    ->select('attack_type', DB::raw('count(*) as total'))
    ->groupBy('attack_type')
    ->get();
foreach ($rows as $r) {
    echo "  {$r->attack_type}: {$r->total}\n";
}
