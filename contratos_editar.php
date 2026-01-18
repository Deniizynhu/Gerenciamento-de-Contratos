<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: contratos.php");
    exit;
}

$id = (int)$_GET['id'];
$msg = '';

// Busca dados do contrato
$stmt = $pdo->prepare("SELECT * FROM contratos WHERE id = ?");
$stmt->execute([$id]);
$contrato = $stmt->fetch();

if (!$contrato) {
    echo "Contrato não encontrado.";
    require 'includes/footer.php';
    exit;
}

// Segurança: cliente só vê o próprio contrato
if ($_SESSION['user_role'] == 'cliente' && $contrato['cliente_id'] != $_SESSION['user_id']) {
    die("Acesso negado.");
}

// Busca todos os clientes (para select)
$stmtC = $pdo->query("SELECT * FROM users WHERE nivel = 'cliente' ORDER BY nome ASC");
$clientes = $stmtC->fetchAll();

// Monta JSON para JS e encontra cliente atual
$clientesJson = [];
$clienteAtual = null;

foreach ($clientes as $c) {
    $endereco = trim(($c['logradouro'] ?? '') . ' ' . ($c['numero'] ?? ''));
    if (!empty($c['complemento'])) $endereco .= ' - ' . $c['complemento'];
    if (!empty($c['bairro']))      $endereco .= ' - ' . $c['bairro'];
    if (!empty($c['cidade']) || !empty($c['estado'])) $endereco .= ' - ' . $c['cidade'] . '/' . $c['estado'];
    if (!empty($c['cep']))         $endereco .= ' - CEP: ' . $c['cep'];

    $clientesJson[$c['id']] = [
        'nome'     => $c['nome'],
        'cpf_cnpj' => $c['cpf_cnpj'],
        'endereco' => $endereco
    ];

    if ($c['id'] == $contrato['cliente_id']) {
        $clienteAtual = $c;
        $clienteEndereco = $endereco;
    }
}

// Atualização do contrato (Admin/Gerente)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['user_role'] != 'cliente') {
    try {
        $sql = "UPDATE contratos SET 
                titulo      = :titulo,
                cliente_id  = :cliente_id,
                servicos    = :servicos,
                descricao   = :descricao,
                valor       = :valor,
                data_inicio = :data_inicio,
                data_fim    = :data_fim,
                status      = :status
                WHERE id    = :id";
        $stmtU = $pdo->prepare($sql);
        $stmtU->execute([
            ':titulo'     => $_POST['titulo'],
            ':cliente_id' => $_POST['cliente_id'],
            ':servicos'   => $_POST['servicos'] ?? '',
            ':descricao'  => $_POST['descricao'] ?? '',
            ':valor'      => str_replace(',', '.', $_POST['valor']),
            ':data_inicio'=> $_POST['data_inicio'],
            ':data_fim'   => $_POST['data_fim'] ?: null,
            ':status'     => $_POST['status'],
            ':id'         => $id
        ]);

        // Recarrega
        $stmt = $pdo->prepare("SELECT * FROM contratos WHERE id = ?");
        $stmt->execute([$id]);
        $contrato = $stmt->fetch();

        // Atualiza cliente atual se mudou
        $clienteAtual = $clientesJson[$contrato['cliente_id']] ?? $clienteAtual;

        $msg = "<div class='alert success'>Contrato atualizado com sucesso!</div>";
    } catch (PDOException $e) {
        $msg = "<div class='alert error'>Erro ao atualizar: " . $e->getMessage() . "</div>";
    }
}

// Controle de edição
$isReadOnly = ($_SESSION['user_role'] == 'cliente');
$attrRead   = $isReadOnly ? 'disabled' : '';

// Monta dados para o texto
$nomeCliente   = $clienteAtual['nome']     ?? '________________________';
$cnpjCliente   = $clienteAtual['cpf_cnpj'] ?? '______________________';
$endCliente    = $clienteEndereco          ?? 'Rua _________________________________________';

$dataInicioBR  = $contrato['data_inicio'] ? date('d/m/Y', strtotime($contrato['data_inicio'])) : '__________________________';
$dataAssinBR   = $dataInicioBR;
$valorMensalBR = number_format($contrato['valor'], 2, ',', '.');

// Lista de serviços (se vazio, usa padrão)
$defaultServicos = "Montagem e Manutenção de Computadores (incluindo Servidor).\nAcesso Remoto quando possível.\nImpressoras (Sistema).\nInfraestrutura e Cabeamento (Mão de obra).\nSistema de Monitoramento (CFTV).\nSistema de telefonia PABX Analógico.";

$servicosTexto = $contrato['servicos'] ?: $defaultServicos;
$linhasServicos = array_filter(array_map('trim', explode("\n", $servicosTexto)));

// Foro fixo (pode ir para config depois)
$foroCidade = "São Paulo";
$foroEstado = "SP";
$localAss   = "Santo André/SP";
?>

<div class="header-title no-print">
    <h2>Contrato #<?= $contrato['id'] ?> - <?= htmlspecialchars($contrato['titulo']) ?></h2>
</div>

<?= $msg ?>

<form method="POST" class="form-cadastro">

    <!-- ÁREA IMPRESSA: CONTRATO COMPLETO -->
    <div id="area-impressao">
        <div class="section-box doc-paper">
            <h3 style="text-align:center; text-transform:uppercase; margin-top:0;">CONTRATO DE PRESTAÇÃO DE SERVIÇOS</h3>

            <p class="legal-text">
            Pelo presente instrumento particular de um lado Dultec Soluções, pessoa Jurídica de direito privado inscrita no CNPJ sob o nº 52.652.825/0001-94, com sede na Rua Altamira, 304 – Parque Novo Oratório, Santo André/SP, neste ato representado pelo proprietário Dênis Baptista da Silva, doravante denominado CONTRATADA e, de outro lado 
            <span id="txt_nome" class="destaque-cliente"><?= htmlspecialchars($nomeCliente) ?></span>, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº <span id="txt_cnpj" class="destaque-cliente"><?= htmlspecialchars($cnpjCliente) ?></span>, com sede na <span id="txt_endereco" class="destaque-cliente"><?= htmlspecialchars($endCliente) ?></span>, neste ato representado na forma prevista em seu Contrato Social, doravante denominada simplesmente CONTRATANTE, têm entre si, justo e contratado o presente, que se regerá pelas seguintes Cláusulas e Condições:
            </p>

            <h4>CLÁUSULA PRIMEIRA – OBJETO</h4>
            <p class="legal-text">
                A CONTRATADA é empresa de prestação de serviços Soluções em Tecnologia e pelo presente instrumento e na melhor forma de direito, obriga-se a executar para a CONTRATANTE os serviços:
            </p>

            <ul class="legal-list">
                <?php foreach($linhasServicos as $linha): ?>
                    <li><?= htmlspecialchars($linha) ?></li>
                <?php endforeach; ?>
            </ul>

            <p class="legal-text">
                PARÁGRAFO PRIMEIRO – A CONTRATADA prestará os serviços constantes do “caput” desta cláusula sem qualquer exclusividade, desempenhando atividades para terceiros em geral, desde que não haja conflito de interesses com o pactuado no presente contrato.
            </p>
            <p class="legal-text">
                PARÁGRAFO SEGUNDO – Os serviços serão prestados com total autonomia, liberdade de horário, sem pessoalidade e sem qualquer subordinação à CONTRATANTE.
            </p>
            <p class="legal-text">
                PARÁGRAFO TERCEIRO – Da mesma forma, a CONTRATANTE poderá contratar outros profissionais ou empresas para prestar os serviços constantes do “caput” desta cláusula sem qualquer exclusividade da CONTRATADA, e sem que haja conflito de interesses com o pactuado no presente contrato.
            </p>

            <h4>CLÁUSULA SEGUNDA – PRAZO</h4>
            <p class="legal-text">
                Os serviços ora contratados serão prestados pelo prazo de 12 (doze) meses, tendo seu início de vigência em <?= $dataInicioBR ?>, sendo que, findo o prazo, e sem manifestação contrária da parte da CONTRATANTE, o mesmo será renovado por novo período de 12 (doze) meses, e haverá uma reavaliação da infraestrutura tecnológica no período de até 15 (quinze) dias antes da renovação automática para verificar qual será a atualização de valor contratual.
            </p>

            <h4>CLÁUSULA TERCEIRA – REMUNERAÇÃO</h4>
            <p class="legal-text">
                Como remuneração pelos serviços a serem prestados, a CONTRATANTE irá remunerar a CONTRATADA, da seguinte forma: pagamento mensal no valor de R$ <?= $valorMensalBR ?>, devendo ser pago até o ÚLTIMO DIA ÚTIL do mês vigente.
            </p>

            <h4>CLÁUSULA QUARTA – ATRASO</h4>
            <p class="legal-text">
                Caso ocorra atraso superior a 3 (três) dias no pagamento do valor mensal deste instrumento, a contar da data de vencimento, a CONTRATANTE entende que haverá uma cobrança de 1,8% (um inteiro e oito décimos por cento) sobre o valor da parcela pelo atraso e de 0,5% (meio por cento) ao dia, que será cobrado por cada dia excedente ao terceiro dia de atraso.
            </p>

            <p class="legal-text">
                PARÁGRAFO PRIMEIRO – A remuneração pelos serviços contratados inclui todos os encargos trabalhistas, sociais, previdenciários, securitários e outros não nominados, gastos e despesas relativos ao exercício dos serviços contratados. Serão apenas acrescidos valores se houver a necessidade da compra de HARDWARES (peças) para melhoria ou para que seja mantido o funcionamento dos microcomputadores, câmeras de segurança ou periféricos relacionados.
            </p>
            <p class="legal-text">
                PARÁGRAFO SEGUNDO – Os pagamentos devem ser efetuados até o último dia útil de cada mês.
            </p>
            <p class="legal-text">
                PARÁGRAFO TERCEIRO – O presente contrato não implica em qualquer vínculo empregatício da CONTRATADA pelos serviços prestados à CONTRATANTE.
            </p>

            <h4>CLÁUSULA QUINTA – OBRIGAÇÕES</h4>
            <p class="legal-text">
                Fica estabelecido que o relacionamento entre CONTRATANTE e CONTRATADA, visando resguardar responsabilidades, será normalmente pela forma escrita, através de consultas e respostas.
            </p>

            <p class="legal-text"><strong>São obrigações exclusivas da CONTRATADA:</strong></p>
            <p class="legal-text">
                a) Prestar os serviços contratados na forma e modo ajustados, dentro das normas e especificações técnicas aplicáveis à espécie, dando plena e total garantia dos mesmos;<br>
                b) Executar os serviços contratados utilizando a melhor técnica e visando sempre atingir o melhor resultado, sob sua exclusiva responsabilidade, sendo-lhe vedada a transferência dos mesmos a terceiros, sem prévia e expressa concordância da CONTRATANTE;<br>
                c) Efetuar o pagamento da remuneração de seus empregados/prepostos, sendo responsável por todos e quaisquer ônus e encargos decorrentes da legislação trabalhista, fiscal e previdenciária, além dos impostos, taxas, obrigações, despesas e afins, que venham a ser reclamados ou tornados obrigatórios em decorrência das obrigações assumidas neste contrato;<br>
                d) Ser a única responsável por qualquer espécie de indenização pleiteada por seus empregados/prepostos, principalmente no tocante a reclamações trabalhistas e acidentes do trabalho;<br>
                e) Assumir a total responsabilidade pelas despesas decorrentes dos serviços ora contratados, seja por exigência legal ou em decorrência da necessidade dos serviços, nada podendo ser cobrado ou exigido da CONTRATANTE, salvo expressa previsão contratual em contrário.
            </p>

            <p class="legal-text"><strong>São obrigações exclusivas da CONTRATANTE:</strong></p>
            <p class="legal-text">
                a) Efetuar o pagamento na forma e modo aprazados;<br>
                b) Comunicar à CONTRATADA sobre as reclamações feitas contra seus empregados/prepostos, bem como com relação a danos por eles causados;<br>
                c) Fornecer à CONTRATADA a documentação solicitada, executar os trabalhos de maneira criteriosa na forma de orientações escritas que serão encaminhadas, colocando à disposição da CONTRATADA as verbas necessárias para desenvolver o trabalho, inclusive contratar por indicação da CONTRATADA os serviços complementares indicados;<br>
                d) Não contratar diretamente funcionário ou preposto da empresa CONTRATADA para a realização dos serviços de responsabilidade desta, pelo período de 06 (seis) meses a contar da data de rescisão do contrato entre as partes.
            </p>

            <h4>CLÁUSULA SEXTA – DISPOSIÇÕES GERAIS</h4>
            <p class="legal-text">
                a) Os serviços estabelecidos por este instrumento não possuem qualquer vinculação trabalhista com a CONTRATANTE, sendo de exclusiva responsabilidade da CONTRATADA quaisquer relações legais com o pessoal necessário à execução dos serviços, possuindo este contrato cunho independente, devendo a CONTRATADA manter em ordem as obrigações previdenciárias decorrentes da vinculação, assumindo responsabilidade integral e exclusiva quanto aos salários e demais encargos trabalhistas e previdenciários de seus empregados/prepostos, principalmente com relação a possíveis reclamatórias trabalhistas, inexistindo solidariedade entre a CONTRATANTE e a CONTRATADA.<br>
                b) A responsabilidade trabalhista, individual ou solidária, eventualmente estabelecida entre CONTRATANTE e o pessoal do quadro de empregados da CONTRATADA, é imputável única e exclusivamente a esta última, que se obriga a ressarcir civilmente à CONTRATANTE os valores que porventura forem despendidos em virtude de vínculo laboral judicialmente declarado, inclusive no que pertine a possíveis danos morais.<br>
                c) As alterações de valores que venham a ser discutidas e aprovadas pelas partes deverão necessariamente ser objeto de Termo Aditivo.<br>
                d) Fica expressamente vedada, no todo ou em parte, a transferência ou cessão dos serviços de que trata o presente instrumento.<br>
                e) É expressamente vedada à CONTRATADA a utilização de trabalhadores menores, púberes e impúberes, para a prestação dos serviços.
            </p>

            <h4>CLÁUSULA SÉTIMA – RESCISÃO</h4>
            <p class="legal-text">
                De pleno direito o presente contrato poderá ser rescindido, a qualquer tempo, desde que comunicado por escrito com 30 (trinta) dias de antecedência. Nesta hipótese, deverá a CONTRATANTE arcar com o pagamento dos serviços já prestados e com multa rescisória equivalente a 30% (trinta por cento) do valor total restante do contrato, salvo pacto diverso entre as partes.
            </p>

            <p class="legal-text">
                PARÁGRAFO PRIMEIRO – O reajuste de valores contratuais é calculado anualmente pela tabela cumulativa do IGP-M (FGV), tomando-se por base a data de 10 de fevereiro de cada ano, ou outro índice que venha a substituí-lo. Para renovação, será realizada uma análise no crescimento do parque tecnológico da empresa para o reajuste ou não dos valores da prestação de serviços citados em contrato.
            </p>

            <p class="legal-text">
                PARÁGRAFO SEGUNDO – O presente contrato também será rescindido de pleno direito nos seguintes casos, sem que assista à CONTRATADA direito a qualquer tipo de indenização, ressarcimento ou multa, por mais especial que seja:<br>
                a) Por insolvência, impetração, solicitação de recuperação judicial ou falência da CONTRATADA;<br>
                b) Pelo não cumprimento de qualquer obrigação da CONTRATADA para com a CONTRATANTE, sejam obrigações originadas no presente instrumento ou em outras relações comerciais, desde que documentadas;<br>
                c) Por inadimplência contratual superior a 30 (trinta) dias após o vencimento da parcela recorrente.
            </p>

            <h4>CLÁUSULA OITAVA – PREJUÍZOS</h4>
            <p class="legal-text">
                A CONTRATADA responderá por qualquer prejuízo que direta ou indiretamente cause à CONTRATANTE, seja por ação ou omissão, sua ou de seus prepostos.
            </p>

            <h4>CLÁUSULA NONA – FORO</h4>
            <p class="legal-text">
                Para dirimir quaisquer controvérsias oriundas do presente contrato, as partes elegem o foro da Comarca de <?= $foroCidade ?> – <?= $foroEstado ?>.
            </p>

            <p class="legal-text" style="margin-top:30px;">
				Por estarem assim justos e de acordo, firmam o presente instrumento em duas vias de igual teor.
			</p>

            <p class="legal-text" style="margin-top:30px; text-align:right;">
                <?= $localAss ?>, <?= $dataAssinBR ?>.
            </p>

            <br><br>

            <div style="display:flex; justify-content:space-between; margin-top:40px;">
                <div style="width:45%; text-align:center;">
                    ___________________________________________<br>
                    DULTEC SOLUÇÕES<br>
                    CONTRATADA
                </div>
                <div style="width:45%; text-align:center;">
                    ___________________________________________<br>
                    <?= htmlspecialchars($nomeCliente) ?><br>
                    CONTRATANTE
                </div>
            </div>
        </div>
    </div>

    <!-- ÁREA DE EDIÇÃO TÉCNICA (não impressa) -->
    <div class="section-box no-print">
        <h3>Dados Técnicos do Contrato</h3>
        <div class="form-grid">
            <div class="col-left">
                <div class="input-group">
                    <label>Título</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($contrato['titulo']) ?>" <?= $attrRead ?> required>
                </div>

                <div class="input-group">
                    <label>Cliente</label>
                    <select name="cliente_id" id="clienteSelect" onchange="atualizarCabecalho()" <?= $attrRead ?> required>
                        <?php foreach($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $contrato['cliente_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Valor (R$)</label>
                    <input type="number" step="0.01" name="valor" value="<?= $contrato['valor'] ?>" <?= $attrRead ?> required>
                </div>
            </div>

            <div class="col-right">
                <div class="input-group">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" value="<?= $contrato['data_inicio'] ?>" <?= $attrRead ?> required>
                </div>

                <div class="input-group">
                    <label>Data Fim</label>
                    <input type="date" name="data_fim" value="<?= $contrato['data_fim'] ?>" <?= $attrRead ?>>
                </div>

                <div class="input-group">
                    <label>Status</label>
                    <?php if($isReadOnly): ?>
                        <input type="text" value="<?= ucfirst($contrato['status']) ?>" disabled>
                    <?php else: ?>
                        <select name="status">
                            <option value="ativo"     <?= $contrato['status']=='ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="pendente"  <?= $contrato['status']=='pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="encerrado" <?= $contrato['status']=='encerrado' ? 'selected' : '' ?>>Encerrado</option>
                        </select>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="input-group">
            <label>Serviços Contratados (texto que aparece na cláusula de objeto)</label>
            <textarea name="servicos" rows="5" style="width:100%; padding:10px; border:1px solid #ced4da; border-radius:4px;" <?= $attrRead ?>><?= htmlspecialchars($servicosTexto) ?></textarea>
        </div>

        <div class="input-group">
            <label>Observações / Complementos</label>
            <textarea name="descricao" rows="4" style="width:100%; padding:10px; border:1px solid #ced4da; border-radius:4px;" <?= $attrRead ?>><?= htmlspecialchars($contrato['descricao']) ?></textarea>
        </div>
    </div>

    <div class="form-footer no-print">
        <a href="contratos.php" class="btn-cancel">Voltar</a>
        <button type="button" onclick="window.print()" class="btn-print">Imprimir</button>

        <?php if(!$isReadOnly): ?>
            <button type="submit" class="btn-save">Salvar</button>
        <?php endif; ?>
    </div>
</form>

<script>
const dbClientes = <?= json_encode($clientesJson) ?>;

function atualizarCabecalho() {
    const id = document.getElementById('clienteSelect').value;
    const txtNome = document.getElementById('txt_nome');
    const txtCnpj = document.getElementById('txt_cnpj');
    const txtEnd  = document.getElementById('txt_endereco');

    if (id && dbClientes[id]) {
        const c = dbClientes[id];
        txtNome.textContent = c.nome || '________________________';
        txtCnpj.textContent = c.cpf_cnpj || '______________________';
        txtEnd.textContent  = c.endereco || 'Rua _________________________________________';
    }
}
window.onload = atualizarCabecalho;
</script>

<style>
    .form-cadastro { background:#f0f3f7; padding:30px; border-radius:8px; }
    .section-box { background:#fff; padding:20px; border-radius:6px; border:1px solid #e1e4e8; margin-bottom:20px; }
    .doc-paper { border-left:4px solid #0056b3; }
    .legal-text { font-family:'Times New Roman', serif; font-size:1rem; line-height:1.6; text-align:justify; }
    .legal-list { margin-left:20px; }
    .destaque-cliente { font-weight:bold; }
    .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:30px; }
    .input-group { margin-bottom:15px; }
    .input-group label { display:block; font-weight:600; margin-bottom:5px; }
    .input-group input, .input-group select, .input-group textarea { width:100%; padding:8px 10px; border:1px solid #ced4da; border-radius:4px; box-sizing:border-box; }
    .form-footer { margin-top:20px; padding-top:15px; border-top:1px solid #ddd; display:flex; justify-content:center; gap:15px; }
    .btn-save, .btn-cancel, .btn-print { width:160px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:4px; border:none; font-size:0.95rem; font-weight:500; cursor:pointer; text-decoration:none; }
    .btn-save { background:#28a745; color:#fff; }
    .btn-cancel { background:#6c757d; color:#fff; }
    .btn-print { background:#17a2b8; color:#fff; }
    .alert { padding:10px; margin-bottom:10px; border-radius:4px; }
    .alert.success { background:#d4edda; color:#155724; }
    .alert.error { background:#f8d7da; color:#721c24; }
    
	/* Tela normal: esconder o contrato, mostrar só os campos de edição */
    @media screen {
        #area-impressao {
            display: none;
        }
    }
    @media print {
        .sidebar, .header-title, .no-print { display:none !important; }
        .main-content { margin:0; padding:0; width:100%; }
        .form-cadastro { padding:0; background:#fff; box-shadow:none; }
        .section-box { border:none; border-radius:0; }
    }
</style>

<?php require 'includes/footer.php'; ?>