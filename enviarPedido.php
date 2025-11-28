<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nomeCliente = $_POST['nomeCliente'] ?? '';
    $telefone = $_POST['telefoneCliente'] ?? '';
    $tipoFesta = $_POST['tipoFesta'] ?? '';
    $tema = $_POST['tema'] ?? '';
    $nomeHomenageado = $_POST['nomeHomenageado'] ?? '';
    $idadeHomenageado = $_POST['idadeHomenageado'] ?? '';
    $dataFesta = $_POST['dataFesta'] ?? '';
    $tamanho = $_POST['tamanho'] ?? '';
    $combo = $_POST['combo'] ?? '';
    $incluiMesa = isset($_POST['incluiMesa']) ? 1 : 0;
    $formaPagamento = $_POST['formaPagamento'] ?? '';
    $valorTotal = $_POST['valorTotal'] ?? 0;
    $adicionais = $_POST['adicionais'] ?? []; // array: [{nome, quantidade, valor}]

    // Inserir pedido principal
    $stmt = $conexao->prepare("
        INSERT INTO pedidos 
        (data_criacao, nome_cliente, telefone, tipo_festa, tema, nome_homenageado, idade_homenageado, data_evento, tamanho_festa, combo_selecionado, inclui_mesa, forma_pagamento, valor_total)
        VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "sssssssssssd",
        $nomeCliente, $telefone, $tipoFesta, $tema, $nomeHomenageado, $idadeHomenageado,
        $dataFesta, $tamanho, $combo, $incluiMesa, $formaPagamento, $valorTotal
    );

    if ($stmt->execute()) {
        $idPedido = $stmt->insert_id;

        // Inserir adicionais, se houver
        if (!empty($adicionais)) {
            $stmtAd = $conexao->prepare("
                INSERT INTO pedidos_adicionais (id_pedido, nome_adicional, quantidade, valor_unidade) 
                VALUES (?, ?, ?, ?)
            ");
            foreach ($adicionais as $ad) {
                $stmtAd->bind_param("isid", $idPedido, $ad['nome'], $ad['quantidade'], $ad['valor']);
                $stmtAd->execute();
            }
            $stmtAd->close();
        }

        echo json_encode(['success' => true, 'message' => 'Pedido enviado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao enviar pedido: ' . $stmt->error]);
    }

    $stmt->close();
    $conexao->close();
}
?>
