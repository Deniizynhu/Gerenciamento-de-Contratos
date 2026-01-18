<?php
require 'config/db.php';
require 'includes/auth.php';
require 'includes/header.php'; // O header já vai carregar o estilo novo (ver passo 3)

// Apenas Admin pode mexer aqui
checkRole(['admin']);

// Busca config atual
$stmt = $pdo->query("SELECT * FROM system_config WHERE id = 1");
$config = $stmt->fetch();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $novoNome = $_POST['sistema_nome'];
        $novoTema = $_POST['cor_tema'];
        $novoLogo = $config['logo_path']; // Mantém o antigo por padrão

        // Processa Upload do Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($ext), ['png', 'jpg', 'jpeg'])) {
                $novoNomeArquivo = 'logo_sistema.' . $ext;
                $destino = 'assets/' . $novoNomeArquivo;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $destino)) {
                    $novoLogo = $destino;
                }
            } else {
                $msg = "<div class='alert error'>Apenas imagens PNG ou JPG são permitidas.</div>";
            }
        }

        // Atualiza no Banco
        $stmt = $pdo->prepare("UPDATE system_config SET sistema_nome = ?, cor_tema = ?, logo_path = ? WHERE id = 1");
        $stmt->execute([$novoNome, $novoTema, $novoLogo]);
        
        // Atualiza a variável local para mostrar na hora
        $config['sistema_nome'] = $novoNome;
        $config['cor_tema'] = $novoTema;
        $config['logo_path'] = $novoLogo;

        // Recarrega a página para aplicar o CSS novo imediatamente
        echo "<script>window.location.href='configuracoes.php';</script>";
        exit;

    } catch (PDOException $e) {
        $msg = "<div class='alert error'>Erro ao salvar: " . $e->getMessage() . "</div>";
    }
}
?>

<div class="header-title">
    <h2>Configurações do Sistema</h2>
</div>

<?= $msg ?>

<form method="POST" enctype="multipart/form-data" class="form-cadastro">
    
    <!-- 1. IDENTIDADE -->
    <div class="section-box">
        <h3>Identidade Visual</h3>
        
        <div class="input-group">
            <label>Nome do Sistema (Título)</label>
            <input type="text" name="sistema_nome" value="<?= htmlspecialchars($config['sistema_nome']) ?>" required>
        </div>

        <div class="input-group">
            <label>Logotipo Atual</label>
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="background: #eee; padding: 10px; border-radius: 4px;">
                    <img src="<?= $config['logo_path'] ?>?t=<?= time() ?>" height="60">
                </div>
                <input type="file" name="logo" accept="image/png, image/jpeg">
            </div>
            <small>Recomendado: PNG transparente (Max: 200px largura)</small>
        </div>
    </div>

    <!-- 2. TEMAS -->
    <div class="section-box">
        <h3>Esquema de Cores</h3>
        <div class="theme-selector">
            
            <!-- TEMA AZUL (PADRÃO) -->
            <label class="theme-card">
                <input type="radio" name="cor_tema" value="azul" <?= $config['cor_tema'] == 'azul' ? 'checked' : '' ?>>
                <div class="theme-preview" style="background: #0056b3; border: 1px solid #ddd;">
                    <span style="background: #fff;"></span>
                </div>
                <strong>Padrão (Azul)</strong>
            </label>

            <!-- TEMA DARK -->
            <label class="theme-card">
                <input type="radio" name="cor_tema" value="dark" <?= $config['cor_tema'] == 'dark' ? 'checked' : '' ?>>
                <div class="theme-preview" style="background: #1e1e2f; border: 1px solid #333;">
                    <span style="background: #27293d;"></span>
                </div>
                <strong>Dark Mode</strong>
            </label>

            <!-- TEMA VERDE (Natureza) -->
            <label class="theme-card">
                <input type="radio" name="cor_tema" value="verde" <?= $config['cor_tema'] == 'verde' ? 'checked' : '' ?>>
                <div class="theme-preview" style="background: #218838; border: 1px solid #ddd;">
                    <span style="background: #f4f9f4;"></span>
                </div>
                <strong>Natureza (Verde)</strong>
            </label>

        </div>
    </div>

    <div class="form-footer">
        <button type="submit" class="btn-save">Salvar Configurações</button>
    </div>
</form>

<style>
    .section-box { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #eee; }
    .section-box h3 { margin-top: 0; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
    
    /* Seletor de Temas */
    .theme-selector { display: flex; gap: 20px; }
    .theme-card { cursor: pointer; text-align: center; }
    .theme-card input { display: none; }
    
    .theme-preview {
        width: 100px; height: 80px; border-radius: 8px; margin-bottom: 10px; position: relative;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s;
    }
    .theme-preview span { /* Simula o menu lateral */
        position: absolute; left: 0; top: 0; bottom: 0; width: 30px; 
        border-top-left-radius: 8px; border-bottom-left-radius: 8px;
    }

    /* Efeito de Seleção */
    .theme-card input:checked + .theme-preview {
        outline: 3px solid var(--primary); transform: scale(1.05);
    }

    /* Reaproveitando estilos */
    .form-cadastro { background: transparent; padding: 0; box-shadow: none; }
    .input-group { margin-bottom: 15px; }
    .input-group label { display: block; font-weight: 600; margin-bottom: 5px; }
    .input-group input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    
    .form-footer { text-align: right; }
    .btn-save { background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 4px; cursor: pointer; font-size: 1rem; }
    .btn-save:hover { opacity: 0.9; }
</style>

<?php require 'includes/footer.php'; ?>