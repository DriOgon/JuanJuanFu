<?php 
header('Content-Type: application/json');

// 模拟不同类型的排行榜数据
$rankingData = [  
    'daily' => [
        ['name' => '用户A', 'count' => 5],
        ['name' => '用户B', 'count' => 7],
        ['name' => '用户C', 'count' => 8],
        ['name' => '用户D', 'count' => 6],
        ['name' => '用户E', 'count' => 7],
        ['name' => '用户F', 'count' => 9],
        ['name' => '用户G', 'count' => 12],
        ['name' => '用户H', 'count' => 11],
        ['name' => '用户I', 'count' => 10],
        ['name' => '用户J', 'count' => 6],
        ['name' => '用户K', 'count' => 5],
        ['name' => '用户L', 'count' => 8],
        ['name' => '用户M', 'count' => 7],
        ['name' => '用户N', 'count' => 9],
        ['name' => '用户O', 'count' => 6],
        ['name' => '用户P', 'count' => 6],
        ['name' => '用户Q', 'count' => 7],
        ['name' => '用户R', 'count' => 10],
        ['name' => '用户S', 'count' => 12],
        ['name' => '用户T', 'count' => 13],
        ['name' => '用户U', 'count' => 14],
        ['name' => '用户V', 'count' => 15],
        ['name' => '用户W', 'count' => 5],
        ['name' => '用户X', 'count' => 6],
        ['name' => '用户Y', 'count' => 8],
        ['name' => '用户Z', 'count' => 9],
    ],
    'weekly' => [
        ['name' => '用户A', 'count' => 10],
        ['name' => '用户B', 'count' => 13],
        ['name' => '用户C', 'count' => 15],
        ['name' => '用户D', 'count' => 14],
        ['name' => '用户E', 'count' => 12],
        ['name' => '用户F', 'count' => 16],
        ['name' => '用户G', 'count' => 17],
        ['name' => '用户H', 'count' => 11],
        ['name' => '用户I', 'count' => 13],
        ['name' => '用户J', 'count' => 12],
        ['name' => '用户K', 'count' => 14],
        ['name' => '用户L', 'count' => 15],
        ['name' => '用户M', 'count' => 13],
        ['name' => '用户N', 'count' => 10],
        ['name' => '用户O', 'count' => 11],
        ['name' => '用户P', 'count' => 15],
        ['name' => '用户Q', 'count' => 14],
        ['name' => '用户R', 'count' => 16],
        ['name' => '用户S', 'count' => 17],
        ['name' => '用户T', 'count' => 18],
        ['name' => '用户U', 'count' => 14],
        ['name' => '用户V', 'count' => 13],
        ['name' => '用户W', 'count' => 12],
        ['name' => '用户X', 'count' => 13],
    ],
    'monthly' => [  // 这里就是你想要的月榜数据
        ['name' => '用户A', 'count' => 40],
        ['name' => '用户B', 'count' => 38],
        ['name' => '用户C', 'count' => 42],
        ['name' => '用户D', 'count' => 41],
        ['name' => '用户E', 'count' => 39],
        ['name' => '用户F', 'count' => 45],
        ['name' => '用户G', 'count' => 48],
        ['name' => '用户H', 'count' => 43],
        ['name' => '用户I', 'count' => 44],
        ['name' => '用户J', 'count' => 42],
        ['name' => '用户K', 'count' => 40],
        ['name' => '用户L', 'count' => 39],
        ['name' => '用户M', 'count' => 41],
        ['name' => '用户N', 'count' => 42],
        ['name' => '用户O', 'count' => 38],
        ['name' => '用户P', 'count' => 50],
        ['name' => '用户Q', 'count' => 47],
        ['name' => '用户R', 'count' => 45],
        ['name' => '用户S', 'count' => 52],
        ['name' => '用户T', 'count' => 48],
        ['name' => '用户U', 'count' => 53],
        ['name' => '用户V', 'count' => 54],
        ['name' => '用户W', 'count' => 55],
        ['name' => '用户X', 'count' => 56],
        ['name' => '用户Y', 'count' => 51],
        ['name' => '用户Z', 'count' => 43],
    ]
];



// 获取查询参数
$type = $_GET['type'] ?? 'daily';  // 默认获取日榜数据

// 检查是否存在该类型的排行榜数据
if (array_key_exists($type, $rankingData)) {
    // 对数据按 'count' 降序排序
    usort($rankingData[$type], function($a, $b) {
        return $b['count'] - $a['count'];  // 降序排序
    });

    // 返回排序后的数据
    echo json_encode($rankingData[$type]);
} else {
    // 返回空数组（若类型不在数据中）
    echo json_encode([]);
}
?>
