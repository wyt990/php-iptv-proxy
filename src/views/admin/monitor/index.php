<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>实时监控 - IPTV 代理系统</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.bootcdn.net/ajax/libs/echarts/5.4.3/echarts.min.js" defer></script>
    <style>
        .card {
            margin-bottom: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .stat-card .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .chart-container {
            height: 400px;
            position: relative;
        }
        .chart-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .recent-errors {
            max-height: 400px;
            overflow-y: auto;
        }
        .table td {
            vertical-align: middle;
        }
        .source-url {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php $currentPage = 'monitor'; ?>
    <?php require __DIR__ . '/../../navbar.php'; ?>

    <div class="container-fluid mx-auto" style="width: 98%;">
        <div class="row">
            <div class="col-md-12">
                <h2>实时监控</h2>
                
                <!-- 统计卡片 -->
                <div class="row mb-4" id="statsCards">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="fas fa-tv text-primary"></i>
                            <div class="stat-value" id="totalChannels">
                                <?= $initialData['data']['channelStats']['total_channels'] ?? '-' ?>
                            </div>
                            <div class="stat-label">总频道数</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="fas fa-check-circle text-success"></i>
                            <div class="stat-value" id="activeChannels">
                                <?= $initialData['data']['channelStats']['active_channels'] ?? '-' ?>
                            </div>
                            <div class="stat-label">正常频道</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="fas fa-exclamation-circle text-danger"></i>
                            <div class="stat-value" id="errorChannels">
                                <?= $initialData['data']['channelStats']['error_channels'] ?? '-' ?>
                            </div>
                            <div class="stat-label">异常频道</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <i class="fas fa-clock text-info"></i>
                            <div class="stat-value" id="avgLatency">
                                <?= round($initialData['data']['channelStats']['avg_latency'] ?? 0) ?>ms
                            </div>
                            <div class="stat-label">平均延时</div>
                        </div>
                    </div>
                </div>

                <!-- 图表区域 -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">24小时性能趋势</h5>
                            </div>
                            <div class="card-body">
                                <div id="performanceChart" class="chart-container">
                                    <div class="chart-loading">加载中...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">分组状态统计</h5>
                            </div>
                            <div class="card-body">
                                <div id="groupChart" class="chart-container">
                                    <div class="chart-loading">加载中...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Redis 监控部分 -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-database"></i> Redis 性能监控
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- 基本信息 -->
                                    <div class="col-md-3">
                                        <div class="card border-0">
                                            <div class="card-body">
                                                <h6 class="text-muted">基本信息</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">版本：</small>
                                                    <span id="redis-version"><?= $initialData['data']['redisStats']['version'] ?? '-' ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">运行时间：</small>
                                                    <span id="redis-uptime"><?= $initialData['data']['redisStats']['uptime_days'] ?? 0 ?> 天</span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">最后保存：</small>
                                                    <span id="redis-last-save"><?= $initialData['data']['redisStats']['last_save_time'] ?? '-' ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 内存使用 -->
                                    <div class="col-md-3">
                                        <div class="card border-0">
                                            <div class="card-body">
                                                <h6 class="text-muted">内存使用</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">当前使用：</small>
                                                    <span id="redis-memory"><?= $initialData['data']['redisStats']['used_memory'] ?? '-' ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">峰值：</small>
                                                    <span id="redis-memory-peak"><?= $initialData['data']['redisStats']['used_memory_peak'] ?? '-' ?></span>
                                                </div>
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar" role="progressbar" id="redis-memory-progress" style="width: 0%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 键值统计 -->
                                    <div class="col-md-3">
                                        <div class="card border-0">
                                            <div class="card-body">
                                                <h6 class="text-muted">键值统计</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">总键数：</small>
                                                    <span id="redis-total-keys"><?= $initialData['data']['redisStats']['total_keys'] ?? 0 ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">过期键数：</small>
                                                    <span id="redis-expired-keys"><?= $initialData['data']['redisStats']['expired_keys'] ?? 0 ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">被驱逐键数：</small>
                                                    <span id="redis-evicted-keys"><?= $initialData['data']['redisStats']['evicted_keys'] ?? 0 ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 性能指标 -->
                                    <div class="col-md-3">
                                        <div class="card border-0">
                                            <div class="card-body">
                                                <h6 class="text-muted">性能指标</h6>
                                                <div class="mb-2">
                                                    <small class="text-muted">命中率：</small>
                                                    <span id="redis-hit-rate"><?= $initialData['data']['redisStats']['hit_rate'] ?? '0%' ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">连接数：</small>
                                                    <span id="redis-connections"><?= $initialData['data']['redisStats']['connected_clients'] ?? 0 ?></span>
                                                </div>
                                                <div class="mb-2">
                                                    <small class="text-muted">从节点数：</small>
                                                    <span id="redis-slaves"><?= $initialData['data']['redisStats']['connected_slaves'] ?? 0 ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 最近错误 -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">最近错误</h5>
                            </div>
                            <div class="card-body recent-errors">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="errorTable">
                                        <thead>
                                            <tr>
                                                <th>频道名称</th>
                                                <th>分组</th>
                                                <th>源地址</th>
                                                <th>检查时间</th>
                                                <th>延时</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js" defer></script>
    <script>
    let performanceChart = null;
    let groupChart = null;
    let isUpdating = false;

    // 初始数据
    const initialData = <?= json_encode($initialData['data']) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // 初始化图表
        initCharts();
        
        // 更新初始数据
        updateStats(initialData);
        
        // 设置定时刷新
        setInterval(fetchStats, <?= $refreshInterval * 1000 ?>);
    });

    function initCharts() {
        performanceChart = echarts.init(document.getElementById('performanceChart'));
        groupChart = echarts.init(document.getElementById('groupChart'));
        
        // 移除加载提示
        document.querySelectorAll('.chart-loading').forEach(el => el.remove());
    }

    async function fetchStats() {
        if (isUpdating) return;
        isUpdating = true;

        try {
            const response = await fetch('/admin/monitor/stats');
            const data = await response.json();
            if (data.success) {
                updateStats(data.data);
            }
        } catch (error) {
            console.error('获取监控数据失败:', error);
        } finally {
            isUpdating = false;
        }
    }

    function updateStats(data) {
        // 使用 requestAnimationFrame 优化 DOM 更新
        requestAnimationFrame(() => {
            // 更新统计卡片
            const stats = data.channelStats;
            document.getElementById('totalChannels').textContent = stats.total_channels;
            document.getElementById('activeChannels').textContent = stats.active_channels;
            document.getElementById('errorChannels').textContent = stats.error_channels;
            document.getElementById('avgLatency').textContent = Math.round(stats.avg_latency || 0) + 'ms';

            // 更新图表
            updatePerformanceChart(data.performanceStats);
            updateGroupChart(data.groupStats);
            
            // 更新错误表格
            updateErrorTable(data.recentErrors);

            // 更新 Redis 统计信息
            if (data.redisStats) {
                updateRedisStats(data.redisStats);
            }
        });
    }

    function updatePerformanceChart(stats) {
        if (!performanceChart) return;

        const hours = stats.map(item => item.hour);
        const latencies = stats.map(item => item.avg_latency);
        const successRates = stats.map(item => (item.successful_checks / item.total_checks * 100).toFixed(2));

        const option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'cross'
                }
            },
            legend: {
                data: ['平均延时', '成功率']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis: {
                type: 'category',
                data: hours,
                axisLabel: {
                    rotate: 45
                }
            },
            yAxis: [
                {
                    type: 'value',
                    name: '延时(ms)',
                    position: 'left'
                },
                {
                    type: 'value',
                    name: '成功率(%)',
                    position: 'right',
                    max: 100,
                    min: 0
                }
            ],
            series: [
                {
                    name: '平均延时',
                    type: 'line',
                    data: latencies,
                    smooth: true,
                    animation: false
                },
                {
                    name: '成功率',
                    type: 'line',
                    yAxisIndex: 1,
                    data: successRates,
                    smooth: true,
                    animation: false
                }
            ]
        };

        performanceChart.setOption(option, true);
    }

    function updateGroupChart(stats) {
        if (!groupChart) return;

        const option = {
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                type: 'scroll'
            },
            series: [
                {
                    name: '频道分布',
                    type: 'pie',
                    radius: '50%',
                    data: stats.map(item => ({
                        name: item.name || '未分组',
                        value: item.total_channels
                    })),
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    },
                    animation: false
                }
            ]
        };

        groupChart.setOption(option, true);
    }

    function updateErrorTable(errors) {
        const tbody = document.querySelector('#errorTable tbody');
        const fragment = document.createDocumentFragment();

        errors.forEach(error => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${error.name}</td>
                <td>${error.group_name || '未分组'}</td>
                <td class="source-url" title="${error.source_url}">
                    ${error.source_url}
                </td>
                <td>${error.checked_at}</td>
                <td>${error.latency}ms</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="checkChannel(${error.id})">
                        重新检查
                    </button>
                </td>
            `;
            fragment.appendChild(tr);
        });

        tbody.innerHTML = '';
        tbody.appendChild(fragment);
    }

    async function checkChannel(id) {
        try {
            const response = await fetch('/admin/channels/check/' + id);
            const data = await response.json();
            if (data.success) {
                fetchStats();
            }
        } catch (error) {
            console.error('检查频道失败:', error);
        }
    }

    function updateRedisStats(stats) {
        // 基本信息
        document.getElementById('redis-version').textContent = stats.version;
        document.getElementById('redis-uptime').textContent = stats.uptime_days + ' 天';
        document.getElementById('redis-last-save').textContent = stats.last_save_time;

        // 内存使用
        document.getElementById('redis-memory').textContent = stats.used_memory;
        document.getElementById('redis-memory-peak').textContent = stats.used_memory_peak;
        const memoryPercent = (parseInt(stats.used_memory) / parseInt(stats.used_memory_peak) * 100).toFixed(2);
        const memoryProgress = document.getElementById('redis-memory-progress');
        memoryProgress.style.width = memoryPercent + '%';
        memoryProgress.className = `progress-bar ${memoryPercent > 80 ? 'bg-danger' : memoryPercent > 60 ? 'bg-warning' : 'bg-success'}`;

        // 键值统计
        document.getElementById('redis-total-keys').textContent = stats.total_keys;
        document.getElementById('redis-expired-keys').textContent = stats.expired_keys;
        document.getElementById('redis-evicted-keys').textContent = stats.evicted_keys;

        // 性能指标
        document.getElementById('redis-hit-rate').textContent = stats.hit_rate;
        document.getElementById('redis-connections').textContent = stats.connected_clients;
        document.getElementById('redis-slaves').textContent = stats.connected_slaves;
    }

    // 监听窗口大小变化，调整图表大小
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            performanceChart?.resize();
            groupChart?.resize();
        }, 250);
    });
    </script>
</body>
</html> 