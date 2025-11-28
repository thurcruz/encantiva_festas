<?php
include('conexao.php');

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : "";

if ($busca === "") {
    // Se não tiver busca, retorna todos os pedidos
    $sql = "SELECT id_pedido, data_criacao, nome_cliente, telefone, data_evento, combo_selecionado, valor_total, status, tema
            FROM pedidos
            ORDER BY data_criacao DESC";

    $resultado = $conexao->query($sql);

    if (!$resultado) {
        echo "<tr><td colspan='9'>Erro ao buscar pedidos.</td></tr>";
        exit();
    }
} else {
    $busca = "%$busca%";
    $sql = "SELECT id_pedido, data_criacao, nome_cliente, telefone, data_evento, combo_selecionado, valor_total, status, tema
            FROM pedidos
            WHERE nome_cliente LIKE ? 
            OR tema LIKE ? 
            OR data_evento LIKE ?
            ORDER BY data_criacao DESC";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sss", $busca, $busca, $busca);
    $stmt->execute();
    $resultado = $stmt->get_result();
}

// Se não tiver nada, retorna linha de aviso
if ($resultado->num_rows == 0) {
    echo "<tr><td colspan='9'>Nenhum resultado encontrado.</td></tr>";
    exit();
}

while ($row = $resultado->fetch_assoc()) {
    $dataCriacao = date('d/m/Y H:i', strtotime($row['data_criacao']));
    $dataEvento = date('d/m/Y', strtotime($row['data_evento']));
    $valor = number_format($row['valor_total'], 2, ',', '.');
    $statusClass = strtolower(str_replace(' ', '', $row['status']));

    echo "
        <tr>
            <td>{$row['id_pedido']}</td>
            <td>$dataCriacao</td>
            <td>
                {$row['nome_cliente']}<br>
                <small>{$row['telefone']}</small>
            </td>
            <td>{$row['tema']}</td>
            <td>$dataEvento</td>
            <td>{$row['combo_selecionado']}</td>
            <td>R$ $valor</td>
            <td><span class='status-badge $statusClass'>{$row['status']}</span></td>
            <td>
                <a href='editar.php?id={$row['id_pedido']}' class='btn-acao btn-editar'>Detalhes/Editar</a>
                <a href='excluir.php?id_pedido={$row['id_pedido']}' class='btn-acao btn-excluir' onclick=\"return confirm('Tem certeza que deseja excluir este pedido?');\">Excluir</a>
            </td>
        </tr>
    ";
}

if (isset($stmt)) $stmt->close();
$conexao->close();
?>
