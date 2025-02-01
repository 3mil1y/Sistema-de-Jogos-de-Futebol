<?php
include('config.php');
include('api.php');

// Pega o nome do time da URL, caso exista
$teamName = isset($_GET['team']) ? $_GET['team'] : '';

// Inicializa as variáveis para armazenar os jogos futuros e passados
$upcomingMatches = [];
$pastMatches = [];

if ($teamName) {
    // Passo 1: Buscar os times disponíveis na API
    $url = API_URL . 'teams/?limit=100';  // Estamos buscando até 100 times, ajuste conforme necessário
    $teamsData = fetchApiData($url);
    
    $teamId = null;
    
    // Passo 2: Encontrar o ID do time que corresponde ao nome fornecido
    foreach ($teamsData['teams'] as $team) {
        if (strtolower($team['name']) === strtolower($teamName)) {
            $teamId = $team['id'];
            break;  // Se o time for encontrado, já podemos parar o loop
        }
    }

    // Se o time foi encontrado, vamos buscar as partidas desse time
    if ($teamId) {
        // Passo 3: Buscar as partidas desse time específico
        $matchesUrl = API_URL . "teams/{$teamId}/matches/";
        $matchesData = fetchApiData($matchesUrl);

        if ($matchesData && isset($matchesData['matches'])) {
            // Dividimos os jogos em dois grupos: futuros e passados
            $currentDate = date('Y-m-d\TH:i:s\Z'); // Data e hora atual no formato ISO 8601
            foreach ($matchesData['matches'] as $match) {
                if (strtotime($match['utcDate']) > strtotime($currentDate)) {
                    // Se o jogo for no futuro, adicionamos à lista de jogos futuros
                    $upcomingMatches[] = $match;
                } else {
                    // Se o jogo já aconteceu, adicionamos à lista de jogos passados
                    $pastMatches[] = $match;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Partidas por Time</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Buscar Partidas por Time</h1>

        <!-- Formulário para o usuário digitar o nome do time -->
        <form action="search.php" method="get">
            <div class="mb-3">
                <label for="team" class="form-label">Nome do time:</label>
                <input type="text" id="team" name="team" value="<?php echo htmlspecialchars($teamName); ?>" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>

        <?php if ($teamName): ?>
            <?php if ($teamId): ?>
                <h2 class="mt-4">Jogos de <?php echo htmlspecialchars($teamName); ?></h2>

                <!-- Exibição dos Jogos Futuros -->
                <?php if ($upcomingMatches && !empty($upcomingMatches)): ?>
                    <h3 class="mt-4">Jogos Programados</h3>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Time Casa</th>
                                <th>Time Visitante</th>
                                <th>Data e Hora</th>
                                <th>Estádio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingMatches as $match): ?>
                                <tr>
                                    <td><?php echo $match['homeTeam']['name']; ?></td>
                                    <td><?php echo $match['awayTeam']['name']; ?></td>
                                    <td><?php echo isset($match['utcDate']) ? date('d/m/Y H:i', strtotime($match['utcDate'])) : 'Data não disponível'; ?></td>
                                    <td><?php echo $match['stadium'] ?? 'Não disponível'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Não há jogos programados para o time <?php echo htmlspecialchars($teamName); ?>.</p>
                <?php endif; ?>

                <!-- Exibição dos Jogos Passados -->
                <?php if ($pastMatches && !empty($pastMatches)): ?>
                    <h3 class="mt-4">Jogos Passados</h3>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Time Casa</th>
                                <th>Time Visitante</th>
                                <th>Data e Hora</th>
                                <th>Placar</th>
                                <th>Estádio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastMatches as $match): ?>
                                <tr>
                                    <td><?php echo $match['homeTeam']['name']; ?></td>
                                    <td><?php echo $match['awayTeam']['name']; ?></td>
                                    <td><?php echo isset($match['utcDate']) ? date('d/m/Y H:i', strtotime($match['utcDate'])) : 'Data não disponível'; ?></td>
                                    <td>
                                        <?php 
                                            // Exibindo o placar do jogo, caso disponível
                                            if (isset($match['score'])) {
                                                $homeScore = $match['score']['fullTime']['homeTeam'] ?? 'N/A';
                                                $awayScore = $match['score']['fullTime']['awayTeam'] ?? 'N/A';
                                                echo $homeScore . ' - ' . $awayScore;
                                            } else {
                                                echo 'Placar não disponível';
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo $match['stadium'] ?? 'Não disponível'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Não há jogos passados para o time <?php echo htmlspecialchars($teamName); ?>.</p>
                <?php endif; ?>

            <?php else: ?>
                <!-- Caso o time não tenha sido encontrado na API -->
                <div class="alert alert-danger mt-4">Time não encontrado. Verifique o nome do time.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Script do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>