<?php
//Arquivo de funções

include('config.php');
include('api.php');

// Função para obter todos os campeonatos disponíveis
function getCompetitions() {
    $url = API_URL . "/competitions";
    return fetchApiData($url);
}

// Função para obter os próximos jogos de uma competição
function getUpcomingMatches($competitionCode) {
    $url = API_URL . "/competitions/" . $competitionCode . "/matches";
    return fetchApiData($url);
}

// Função para obter os últimos resultados de uma competição
function getLastResults($competitionCode) {
    $url = API_URL . "/competitions/" . $competitionCode . "/matches?status=FINISHED";
    return fetchApiData($url);
}