<?php
include("functions.php");
//Busca os campeonatos para gerar o menu select
$competitions = getCompetitions();

// Verifique se a resposta contém a chave competitions e se não está vazia 

if (isset($competitions['competitions']) && !empty($competitions['competitions'])) {
    $competitionsList = $competitions['competitions'];
} else {
    $competitionsList = [];
}

// Definir o campeonato selecionado, padrão será o Campeonato Brasileiro
$competition = isset($_POST['competition']) ? $_POST['competition'] : 'BSA'; // Default Campeonato Brasileiro Série A
$action = isset($_POST['action']) ? $_POST['action'] : 'upcoming'; // 'upcoming' ou 'last'

// Obter os dados baseados no campeonato selecionado
if ($action == 'upcoming') {
    // Carregar próximos jogos da competição selecionada
    $data = getUpcomingMatches($competition);
} else {
    // Carregar os últimos resultados da competição selecionada
    $lastResults = getLastResults($competition);
}

// Variáveis de mensagem de erro
$errorMessage = "";
$noGamesMessage = "";
$noRecentGamesMessage = "";

// Verificar se a resposta da API está vazia ou contém erro
if (empty($data) && empty($lastResults)) {
    $errorMessage = "Não foi possível carregar os jogos. Se o problema persistir, entre em contato com o administrador do sistema.";
} elseif (!isset($data['matches']) || empty($data['matches'])) {
    $noGamesMessage = "No momento este campeonato não possui jogos programados! Aproveite para conhecer novas ligas!";
} elseif ($action == 'last' && (!isset($lastResults['matches']) || empty($lastResults['matches']))) {
    $noRecentGamesMessage = "Este campeonato não possui jogos recentes. Que tal dar uma olhada em outros do nosso catálogo?";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Futebol - Jogos Programados</title>
    <!-- Link do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container py-4">
        <h1 class="text-center mb-4">Informações de Futebol</h1>

        <!-- Formulário para seleção de campeonato e ação -->
        <form method="POST" action="index.php">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="competition" class="form-label">Selecione o Campeonato:</label>
                    <select name="competition" id="competition" class="form-select">
                        <?php if (!empty($competitionsList)): ?>
                            <?php foreach ($competitionsList as $competitionItem): ?>
                                <option value="<?php echo $competitionItem['id']; ?>" <?php echo ($competition == $competitionItem['id']) ? 'selected' : ''; ?>>
                                    <?php echo $competitionItem['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option disabled>Não há campeonatos disponíveis no momento.</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <a href="search.php" class="btn btn-outline-primary mt-4">Busca por times</a>
                </div>
            </div>

            <h3>O que deseja hoje?</h3>
            <div class="d-grid gap-2 d-md-block">
                <button type="submit" name="action" value="upcoming" class="btn btn-primary">Próximos Jogos</button>
                <button type="submit" name="action" value="last" class="btn btn-secondary">Últimos Resultados</button>
            </div>
        </form>

        <!-- Exibição das mensagens de erro -->
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger mt-4"><?php echo $errorMessage; ?></div>
        <?php elseif (!empty($noGamesMessage)): ?>
            <div class="alert alert-info mt-4"><?php echo $noGamesMessage; ?></div>
        <?php elseif (!empty($noRecentGamesMessage)): ?>
            <div class="alert alert-info mt-4"><?php echo $noRecentGamesMessage; ?></div>
        <?php endif; ?>

        <!-- Exibição dos jogos -->
        <?php if ($action == 'upcoming' && isset($data['matches']) && !empty($data['matches'])): ?>
            <h2 class="mt-4">Próximos Jogos</h2>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Time Casa</th>
                        <th>Time Visitante</th>
                        <th>Data e Hora</th>
                        <th>Estádio</th>
                        <th>Placar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['matches'] as $match): ?>
                        <tr>
                            <td><?php echo $match['homeTeam']['name'] ?? 'Desconhecido'; ?></td>
                            <td><?php echo $match['awayTeam']['name'] ?? 'Desconhecido'; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($match['utcDate'])) ?? 'Data não disponível'; ?></td>
                            <td><?php echo $match['stadium'] ?? 'Não disponível'; ?></td>
                            <td>
                            <?php
                                if (isset($match['score']['fullTime'])) {
                                    $homeScore = isset($match['score']['fullTime']['homeTeam']) ? $match['score']['fullTime']['homeTeam'] : 'N/A';
                                    $awayScore = isset($match['score']['fullTime']['awayTeam']) ? $match['score']['fullTime']['awayTeam'] : 'N/A';
                                    echo "{$homeScore}x{$awayScore}";
                                } else {
                                    echo 'Placar não disponível';
                                }
                            ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($action == 'last' && isset($lastResults['matches']) && !empty($lastResults['matches'])): ?>
            <h3 class="mt-4">Últimos Resultados</h3>
            <ul class="list-group">
                <?php foreach ($lastResults['matches'] as $match): ?>
                    <li class="list-group-item">
                        <?php echo $match['homeTeam']['name']; ?> 
                        <?php echo $match['score']['fullTime']['home']; ?> x 
                        <?php echo $match['score']['fullTime']['away']; ?> 
                        <?php echo $match['awayTeam']['name']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>