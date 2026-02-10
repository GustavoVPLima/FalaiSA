from flask import Flask, render_template, request, redirect, url_for, session, flash, jsonify
from datetime import datetime
import os
app = Flask(__name__)
app.secret_key = 'segredo_super_importante'
import mysql.connector
import os
import uuid
from werkzeug.utils import secure_filename


app.config['UPLOAD_FOLDER_CHAT'] = 'static/uploads/chat'
app.config['ALLOWED_CHAT_EXTENSIONS'] = {
    'texto': {'txt'},
    'imagem': {'png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'},
    'arquivo': {'pdf', 'doc', 'docx', 'zip', 'rar', 'mp3', 'mp4', 'wav'},
    'audio': {'mp3', 'wav', 'ogg', 'm4a'}
}
app.config['MAX_CHAT_FILE_SIZE'] = 10 * 1024 * 1024  # 10MB
app.config['ALLOWED_EXTENSIONS'] = {'png', 'jpg', 'jpeg'}
app.config['MAX_CONTENT_LENGHT'] = 2 * 1024 * 1024
app.config['UPLOAD_FOLDER_USUARIOS'] ='static/uploads/usuarios'
app.config['UPLOAD_FOLDER_COMUNIDADES'] ='static/uploads/comunidades'

if not os.path.exists(app.config['UPLOAD_FOLDER_USUARIOS']):
    os.makedirs(app.config['UPLOAD_FOLDER_USUARIOS'])
if not os.path.exists(app.config['UPLOAD_FOLDER_COMUNIDADES']):
    os.makedirs(app.config['UPLOAD_FOLDER_COMUNIDADES'])
if not os.path.exists(app.config['UPLOAD_FOLDER_CHAT']):
    os.makedirs(app.config['UPLOAD_FOLDER_CHAT'])

def allowed_chat_file(filename, filetype):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_CHAT_EXTENSIONS'].get(filetype, set())

def allowed_file(filename):
    return '.' in filename and \
        filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_EXTENSIONS']

def conectar():
    return mysql.connector.connect(
        host="tini.click",
        user="falai_sa",
        password="b208c8605208eb3f5f5204f28d91cec4",
        database="falai_sa",
    )

@app.route('/')
def login():
    if session.get('logado'):
        if session.get('tipo_usuario') == 'admin':
            return redirect(url_for('admin_index'))
        else:
            return redirect(url_for('index'))
    return render_template('login.html')


@app.route('/login', methods=['POST'])
def login_post():
    usuario = request.form.get('usuario')
    senha = request.form.get('senha')

    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    cursor.execute("SELECT *, 'usuario' as tipo FROM tb_usuario WHERE nm_login=%s AND ds_senha=%s", (usuario, senha))
    user = cursor.fetchone()

    if not user:
        cursor.execute("SELECT*, 'admin' as tipo FROM tb_admin WHERE nm_login=%s AND ds_senha=%s", (usuario, senha))
        user = cursor.fetchone()
    cursor.close()
    conexao.close()

    if user:
        session['logado'] = True
        session['tipo_usuario'] = user['tipo'] 
        session['inadmin'] = (user['tipo'] == 'admin') 
        if user['tipo'] == 'admin':
            session['usuario'] = user['nm_login']
            session['idadm'] = user['id_admin']
            session['isadmin'] = user['isadmin']      
            return redirect(url_for('admin_index'))  
        else:
            session['usuario'] = user['nm_login']
            session['id'] = user['id_usuario']
            foto_perfil = user['img_perfil'] if user['img_perfil'] else 'perfilplaceholder.png'
            session['foto_perfil'] = foto_perfil
        return redirect(url_for('index'))
    else:
        flash('Usuário ou senha incorretos.', 'erro')
        return redirect(url_for('login'))
@app.route('/criarcomunidade', methods=['GET', 'POST'])
def criar_comunidade():
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    flashed_messages = session.get('_flashes', [])
    if flashed_messages:
        for i, (category, message) in enumerate(flashed_messages):
            if 'cadastrada' in message.lower() or 'conta' in message.lower():
                flashed_messages.pop(i)
                session['_flashes'] = flashed_messages
                break
    
    if request.method == 'POST':
        nome_comunidade = request.form.get('nome_comunidade')
        max_usuario = request.form.get('max_usuario') 
        sem_limite = request.form.get('sem_limite')
        desc_comunidade = request.form.get('descricao')
        imagem_perfil = 'perfilplaceholder.png'
        id_criador = session.get('id')    

        if not desc_comunidade:
            desc_comunidade = 'Comunidade sem descrição'    
        
        if 'imagem_comunidade' in request.files:
            file = request.files['imagem_comunidade']
            if file and file.filename != '' and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                timestamp = str(int(time.time()))
                name, ext = os.path.splitext(filename)
                filename = f"comunidade_{timestamp}{ext}"
                file_path = os.path.join(app.config['UPLOAD_FOLDER_COMUNIDADES'], filename)
                file.save(file_path)
                imagem_perfil = filename
            elif file and file.filename != '':
                flash('Tipo de arquivo não permitido!', 'erro')
                return render_template('criar_comunidade.html')

        if sem_limite:
            max_usuario = 0

        if nome_comunidade and max_usuario:
            conexao = conectar()
            cursor = conexao.cursor()

            try:
                if int(max_usuario) > 0 and int(max_usuario) < 2:
                    flash('Minimo de usuários é 2', 'erro')
                    return render_template('criar_comunidade.html')
                
                sql = 'INSERT INTO tb_comunidade (nm_comunidade, criado_por, ds_comunidade, max_usuario, img_perfil, dt_criacao) VALUES (%s,%s,%s,%s,%s,%s)'
                valores = (nome_comunidade, id_criador, desc_comunidade, int(max_usuario), imagem_perfil, datetime.now())
                cursor.execute(sql,valores)
                conexao.commit()

                comunidade_id = cursor.lastrowid

                cursor.execute(
                    "INSERT INTO tb_usuario_comunidade (id_usuario, id_comunidade) VALUES (%s,%s)",(id_criador, comunidade_id)
                )
                conexao.commit()

                if int(max_usuario) == 0:
                    flash('Comunidade criada com sucesso!', 'sucesso')
                    return redirect(url_for('minhas_comunidades'))
                else:
                    flash('Comunidade criada com sucesso!', 'sucesso')
                    return redirect(url_for('minhas_comunidades'))  
            except mysql.connector.Error as e:
                conexao.rollback()
                flash('Erro ao criar comunidade! Tente novamente', 'erro')
                print(f'Erro {e}')
            
            finally:
                cursor.close()
                conexao.close()
        else:
            flash('Preencha todos os campos!', 'erro')
    return render_template('criar_comunidade.html')

def allowed_chat_file(filename, filetype):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in app.config['ALLOWED_CHAT_EXTENSIONS'].get(filetype, set())

@app.route('/chatcomunidade/<int:id_comunidade>')
def chat_comunidade(id_comunidade):
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é membro da comunidade
        cursor.execute("""
            SELECT 1 FROM tb_usuario_comunidade 
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        if not cursor.fetchone():
            flash('Você não é membro desta comunidade.', 'erro')
            return redirect(url_for('index'))
        
        # Buscar informações da comunidade
        cursor.execute("""
            SELECT 
                c.*,
                u.nm_login as nome_criador,
                (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) as total_membros
            FROM tb_comunidade c
            INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
            WHERE c.id_comunidade = %s
        """, (id_comunidade,))
        
        comunidade = cursor.fetchone()
        
        if not comunidade:
            flash('Comunidade não encontrada.', 'erro')
            return redirect(url_for('index'))
        
        # Buscar membros da comunidade
        cursor.execute("""
            SELECT 
                u.id_usuario,
                u.nm_login,
                u.img_perfil
                FROM tb_usuario_comunidade uc
                INNER JOIN tb_usuario u ON uc.id_usuario = u.id_usuario
                WHERE uc.id_comunidade = %s
                ORDER BY 
                CASE WHEN u.id_usuario = %s THEN 0 ELSE 1 END,
                u.nm_login
        """, (id_comunidade, id_usuario))
        
        membros = cursor.fetchall()
        
    except mysql.connector.Error as e:
        flash(f'Erro ao carregar chat: {str(e)}', 'erro')
        return redirect(url_for('minhas_comunidades'))
    finally:
        cursor.close()
        conexao.close()
    
    return render_template('chat_comunidade.html',
                         usuario=session['usuario'],
                         comunidade=comunidade,
                         membros=membros,
                         total_membros=len(membros))

@app.route('/chatcomunidade/<int:id_comunidade>/mensagens')
def carregar_mensagens(id_comunidade):
    """Carregar mensagens da comunidade (API)"""
    if not session.get('logado'):
        return jsonify({'success': False, 'error': 'Não autenticado'})
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é membro
        cursor.execute("""
            SELECT 1 FROM tb_usuario_comunidade 
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        if not cursor.fetchone():
            return jsonify({'success': False, 'error': 'Acesso negado'})
        
        # Buscar últimas 50 mensagens
        cursor.execute("""
            SELECT 
                c.id_chat,
                c.id_chat_comunidade,
                c.id_chat_usuario,
                c.mensagem,
                c.tipo,
                c.arquivo_url,
                c.dt_envio,
                c.lida,
                u.nm_login as usuario_nome,
                u.img_perfil as usuario_avatar
            FROM tb_chat c
            INNER JOIN tb_usuario u ON c.id_chat_usuario = u.id_usuario
            WHERE c.id_chat_comunidade = %s
            ORDER BY c.dt_envio DESC
            LIMIT 50
        """, (id_comunidade,))
        
        mensagens = cursor.fetchall()
        
        # Inverter ordem para mostrar as mais antigas primeiro
        mensagens.reverse()
        
        return jsonify({
            'success': True,
            'mensagens': mensagens
        })
        
    except mysql.connector.Error as e:
        return jsonify({'success': False, 'error': str(e)})
    finally:
        cursor.close()
        conexao.close()

@app.route('/chatcomunidade/<int:id_comunidade>/novas')
def carregar_novas_mensagens(id_comunidade):
    """Carregar novas mensagens desde o último ID (API)"""
    if not session.get('logado'):
        return jsonify({'success': False, 'error': 'Não autenticado'})
    
    id_usuario = session.get('id')
    ultima_mensagem_id = request.args.get('ultima', 0, type=int)
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é membro
        cursor.execute("""
            SELECT 1 FROM tb_usuario_comunidade 
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        if not cursor.fetchone():
            return jsonify({'success': False, 'error': 'Acesso negado'})
        
        # Buscar novas mensagens
        cursor.execute("""
            SELECT 
                c.id_chat,
                c.id_chat_comunidade,
                c.id_chat_usuario,
                c.mensagem,
                c.tipo,
                c.arquivo_url,
                c.dt_envio,
                c.lida,
                u.nm_login as usuario_nome,
                u.img_perfil as usuario_avatar
                FROM tb_chat c
                INNER JOIN tb_usuario u ON c.id_chat_usuario = u.id_usuario
                WHERE c.id_chat_comunidade = %s AND c.id_chat > %s
                ORDER BY c.dt_envio ASC
            """, (id_comunidade, ultima_mensagem_id))
        
        mensagens = cursor.fetchall()
        
        return jsonify({
            'success': True,
            'mensagens': mensagens
        })
        
    except mysql.connector.Error as e:
        return jsonify({'success': False, 'error': str(e)})
    finally:
        cursor.close()
        conexao.close()

@app.route('/chatcomunidade/<int:id_comunidade>/enviar', methods=['POST'])
def enviar_mensagem_chat(id_comunidade):
    """Enviar mensagem no chat (API)"""
    if not session.get('logado'):
        return jsonify({'success': False, 'error': 'Não autenticado'})
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é membro
        cursor.execute("""
            SELECT 1 FROM tb_usuario_comunidade 
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        if not cursor.fetchone():
            return jsonify({'success': False, 'error': 'Acesso negado'})
        
        mensagem_texto = request.form.get('mensagem', '').strip()
        arquivo = request.files.get('arquivo')
        
        if not mensagem_texto and not arquivo:
            return jsonify({'success': False, 'error': 'Mensagem ou arquivo necessário'})
        
        tipo = 'texto'
        arquivo_url = None
        
        # Processar upload de arquivo
        if arquivo and arquivo.filename:
            if arquivo.content_length > app.config['MAX_CHAT_FILE_SIZE']:
                return jsonify({'success': False, 'error': 'Arquivo muito grande (max 10MB)'})
            
            # Determinar tipo do arquivo
            filename = secure_filename(arquivo.filename)
            ext = filename.rsplit('.', 1)[1].lower() if '.' in filename else ''
            
            if ext in app.config['ALLOWED_CHAT_EXTENSIONS']['imagem']:
                tipo = 'imagem'
            elif ext in app.config['ALLOWED_CHAT_EXTENSIONS']['audio']:
                tipo = 'audio'
            else:
                tipo = 'arquivo'
            
            # Gerar nome único para o arquivo
            unique_filename = f"{uuid.uuid4().hex}_{filename}"
            filepath = os.path.join(app.config['UPLOAD_FOLDER_CHAT'], unique_filename)
            arquivo.save(filepath)
            arquivo_url = unique_filename
        
        # Inserir mensagem no banco
        cursor.execute("""
            INSERT INTO tb_chat (id_chat_comunidade, id_chat_usuario, mensagem, tipo, arquivo_url)
            VALUES (%s, %s, %s, %s, %s)
        """, (id_comunidade, id_usuario, mensagem_texto, tipo, arquivo_url))
        
        conexao.commit()
        
        # Buscar a mensagem inserida
        mensagem_id = cursor.lastrowid
        cursor.execute("""
            SELECT 
                c.*,
                u.nm_login as usuario_nome,
                u.img_perfil as usuario_avatar
            FROM tb_chat c
            INNER JOIN tb_usuario u ON c.id_chat_usuario = u.id_usuario
            WHERE c.id_chat = %s
        """, (mensagem_id,))
        
        mensagem = cursor.fetchone()
        
        return jsonify({
            'success': True,
            'mensagem': mensagem
        })
        
    except mysql.connector.Error as e:
        conexao.rollback()
        return jsonify({'success': False, 'error': str(e)})
    finally:
        cursor.close()
        conexao.close()

@app.route('/chatcomunidade/<int:id_comunidade>/visualizar', methods=['POST'])
def marcar_mensagens_visualizadas(id_comunidade):
    """Marcar mensagens como visualizadas (API)"""
    if not session.get('logado'):
        return jsonify({'success': False, 'error': 'Não autenticado'})
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor()
    
    try:
        # Atualizar última visualização
        cursor.execute("""
            UPDATE tb_usuario_comunidade 
            SET ultima_visualizacao = CURRENT_TIMESTAMP
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        # Marcar mensagens como lidas
        cursor.execute("""
            UPDATE tb_chat 
            SET lida = TRUE 
            WHERE id_chat_comunidade = %s 
            AND id_chat_usuario != %s
            AND lida = FALSE
        """, (id_comunidade, id_usuario))
        
        conexao.commit()
        
        return jsonify({'success': True})
        
    except mysql.connector.Error as e:
        conexao.rollback()
        return jsonify({'success': False, 'error': str(e)})
    finally:
        cursor.close()
        conexao.close()

@app.route('/chatcomunidade/<int:id_comunidade>/estatisticas')
def estatisticas_chat(id_comunidade):
    """Obter estatísticas do chat (API)"""
    if not session.get('logado'):
        return jsonify({'success': False, 'error': 'Não autenticado'})
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é membro
        cursor.execute("""
            SELECT 1 FROM tb_usuario_comunidade 
            WHERE id_usuario = %s AND id_comunidade = %s
        """, (id_usuario, id_comunidade))
        
        if not cursor.fetchone():
            return jsonify({'success': False, 'error': 'Acesso negado'})
        
        # Contar mensagens não lidas
        cursor.execute("""
            SELECT COUNT(*) as nao_lidas
            FROM tb_chat
            WHERE id_chat_comunidade = %s 
            AND id_chat_usuario != %s
            AND lida = FALSE
        """, (id_comunidade, id_usuario))
        
        nao_lidas = cursor.fetchone()['nao_lidas']
        
        # Última atividade
        cursor.execute("""
            SELECT MAX(dt_envio) as ultima_atividade
            FROM tb_chat
            WHERE id_chat_comunidade = %s
        """, (id_comunidade,))
        
        ultima_atividade = cursor.fetchone()['ultima_atividade']
        
        return jsonify({
            'success': True,
            'nao_lidas': nao_lidas,
            'ultima_atividade': ultima_atividade.strftime('%Y-%m-%d %H:%M:%S') if ultima_atividade else None
        })
        
    except mysql.connector.Error as e:
        return jsonify({'success': False, 'error': str(e)})
    finally:
        cursor.close()
        conexao.close()

@app.route('/cadastrousuario', methods=['GET', 'POST'])
def cadastro_usuario():
    if request.method == 'POST':
        nome_login = request.form.get('nome_login')
        email = request.form.get('email')
        senha = request.form.get('senha')
        telefone = request.form.get('telefone')
        foto_perfil = 'perfilplaceholder.png'
        bio = request.form.get('biografia')

        if not bio:
            bio = 'Ainda não possui biografia'
        
        if 'foto_perfil' in request.files:
            file = request.files['foto_perfil']
            if file and file.filename != '' and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                timestamp = str(int(time.time()))
                name, ext = os.path.splitext(filename)
                filename = f"usuario_{timestamp}{ext}"
                file_path = os.path.join(app.config['UPLOAD_FOLDER_USUARIOS'], filename)
                file.save(file_path)
                foto_perfil = filename
            elif file and file.filename != '':
                flash('Tipo de arquivo não permitido!', 'erro')
                return render_template('cadastro_usuario.html')
            
        if nome_login and email and senha and telefone:
            conexao = conectar()
            cursor = conexao.cursor()
            try:
                sql = 'INSERT INTO tb_usuario(nm_login, nm_email,ds_senha, nr_numero, img_perfil, ds_usuario, dt_cadastro) VALUES (%s, %s,%s,%s, %s, %s,%s)'
                valores = (nome_login , email, senha, telefone, foto_perfil, bio, datetime.now())
                cursor.execute(sql, valores)
                conexao.commit()

                cursor.execute('SELECT id_usuario FROM tb_usuario WHERE nm_login=%s', (nome_login,))
                novo_usuario = cursor.fetchone()
                session['logado'] = True
                session['tipo_usuario'] = 'usuario'
                session['inadmin'] = False
                session['usuario'] = nome_login
                session['foto_perfil'] = foto_perfil
                session['id'] = novo_usuario[0]
                cursor.close()
                conexao.close()
                flash('Conta cadastrada com sucesso!', 'sucesso')
                return redirect(url_for('login'))
            
            except mysql.connector.Error as e:
                conexao.rollback()
                cursor.close()
                conexao.close()

                if e.errno == 1062:
                    flash('Nome de usuário, e-mail ou telefone já está em uso.', 'erro')
                    return render_template('cadastro_usuario.html')
    return render_template('cadastro_usuario.html')

@app.route('/sobrenos')
def sobre_nos():
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)

    cursor.close()
    conexao.close()
    return render_template('sobre_nos.html')

@app.route('/minhascomunidades')
def minhas_comunidades():
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))

    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)

    cursor.execute("""
        SELECT 
            c.id_comunidade,
            c.nm_comunidade,
            c.max_usuario,
            c.img_perfil,
            c.criado_por,
            u.nm_login as nome_criador,
            COUNT(m.id_usuario) as total_membros,
            CASE 
                WHEN c.criado_por = %s THEN 1 
                ELSE 0 
            END as usuario_eh_criador      
        FROM tb_comunidade c
        INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
        INNER JOIN tb_usuario_comunidade uc ON c.id_comunidade = uc.id_comunidade
        LEFT JOIN tb_usuario_comunidade m ON c.id_comunidade = m.id_comunidade
        WHERE uc.id_usuario = %s
        GROUP BY c.id_comunidade, c.nm_comunidade, c.criado_por, u.nm_login
        ORDER BY 
            CASE WHEN c.criado_por = %s THEN 0 ELSE 1 END,  -- Comunidades criadas primeiro
            c.nm_comunidade ASC
    """, (session['id'], session['id'], session['id']))

    comunidades = cursor.fetchall()
    cursor.close()
    conexao.close()
    
    return render_template('minhas_comunidades.html', 
                        usuario=session['usuario'], 
                        comunidades=comunidades)

@app.route('/saircomunidade/<int:id_comunidade>')
def sair_comunidade(id_comunidade):
    """Permite que um usuário saia de uma comunidade (se não for o criador)"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    
    id_usuario = session.get('id')
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário é o criador da comunidade
        cursor.execute(
            "SELECT criado_por, nm_comunidade FROM tb_comunidade WHERE id_comunidade = %s",
            (id_comunidade,)
        )
        
        comunidade = cursor.fetchone()
        
        if not comunidade:
            flash('Comunidade não encontrada.', 'erro')
            return redirect(url_for('minhas_comunidades'))
        
        # Verificar se o usuário é o criador
        if comunidade['criado_por'] == id_usuario:
            flash(f'Você não pode sair da comunidade "{comunidade["nm_comunidade"]}" porque você é o criador. Se desejar, pode excluí-la na página de Gerenciar Comunidades.', 'erro')
            return redirect(url_for('minhas_comunidades'))
        
        # Verificar se o usuário está na comunidade
        cursor.execute(
            "SELECT * FROM tb_usuario_comunidade WHERE id_usuario = %s AND id_comunidade = %s",
            (id_usuario, id_comunidade)
        )
        
        if not cursor.fetchone():
            flash('Você não está nesta comunidade.', 'erro')
            return redirect(url_for('minhas_comunidades'))
        
        # Remover usuário da comunidade
        cursor.execute(
            "DELETE FROM tb_usuario_comunidade WHERE id_usuario = %s AND id_comunidade = %s",
            (id_usuario, id_comunidade)
        )
        
        conexao.commit()
        
        flash(f'Você saiu da comunidade "{comunidade["nm_comunidade"]}" com sucesso!', 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash(f'Erro ao sair da comunidade: {str(e)}', 'erro')
        print(f'Erro ao sair da comunidade: {e}')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('minhas_comunidades'))

@app.route('/entrarcomunidade/<int:id_comunidade>')
def entrar_comunidade(id_comunidade):
    """Permite que um usuário entre em uma comunidade"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    
    id_usuario = session.get('id')
    
    if not id_usuario:
        flash('Usuário não identificado.', 'erro')
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Verificar se o usuário já está na comunidade
        cursor.execute(
            "SELECT * FROM tb_usuario_comunidade WHERE id_usuario = %s AND id_comunidade = %s",
            (id_usuario, id_comunidade)
        )
        
        if cursor.fetchone():
            flash('Você já está nesta comunidade.', 'info')
            return redirect(url_for('index'))
        
        # Verificar se a comunidade existe
        cursor.execute(
            "SELECT max_usuario, nm_comunidade FROM tb_comunidade WHERE id_comunidade = %s",
            (id_comunidade,)
        )
        
        comunidade = cursor.fetchone()
        
        if not comunidade:
            flash('Comunidade não encontrada.', 'erro')
            return redirect(url_for('index'))
        
        # Verificar número atual de membros
        cursor.execute(
            "SELECT COUNT(*) as total_membros FROM tb_usuario_comunidade WHERE id_comunidade = %s",
            (id_comunidade,)
        )
        
        total_membros = cursor.fetchone()['total_membros']
        
        # Verificar limite de membros (se houver)
        if comunidade['max_usuario'] > 0 and total_membros >= comunidade['max_usuario']:
            flash(f'A comunidade "{comunidade["nm_comunidade"]}" está cheia! Limite: {comunidade["max_usuario"]} membros.', 'erro')
            return redirect(url_for('index'))
        
        # Adicionar usuário à comunidade
        cursor.execute(
            "INSERT INTO tb_usuario_comunidade (id_usuario, id_comunidade) VALUES (%s, %s)",
            (id_usuario, id_comunidade)
        )
        
        conexao.commit()
        
        flash(f'Você entrou na comunidade "{comunidade["nm_comunidade"]}"!', 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash(f'Erro ao entrar na comunidade: {str(e)}', 'erro')
        print(f'Erro ao entrar na comunidade: {e}')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('index'))

@app.route('/minhascomunidadescriadas')
def gerenciar_comunidades():
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))

    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)

    cursor.execute("""
        SELECT 
            c.id_comunidade,
            c.nm_comunidade,
            c.max_usuario,
            c.img_perfil,
            u.nm_login as nome_criador,
            COUNT(m.id_usuario) as total_membros       
        FROM tb_comunidade c
        INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
        LEFT JOIN tb_usuario_comunidade m ON c.id_comunidade = m.id_comunidade
        WHERE c.criado_por  = %s
        GROUP BY c.id_comunidade, c.nm_comunidade, u.nm_login
        ORDER BY c.id_comunidade DESC
    """, (session['id'],))

    comunidades = cursor.fetchall()
    cursor.close()
    conexao.close()

    return render_template('gerenciar_comunidades.html', 
                           usuario = session['usuario'],
                           comunidades=comunidades)

@app.route('/excluircomunidade/<int:id_comunidade>')
def excluir_comunidade(id_comunidade):
    if not session.get('logado'):
        return redirect(url_for('login'))   
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    conexao = conectar()
    cursor = conexao.cursor()
    cursor.execute('DELETE FROM tb_comunidade WHERE id_comunidade=%s AND criado_por=%s', (id_comunidade, session['id']))
    conexao.commit()
    cursor.close()
    conexao.close()
    flash('Você excluiu a comunidade', 'sucesso')
    return redirect(url_for('gerenciar_comunidades'))

@app.route('/editarcomunidade/<int:id_comunidade>', methods=['GET', 'POST'])
def editar_comunidade(id_comunidade):
    if not session.get('logado'):
        return redirect(url_for('login'))   
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)

    if request.method == "POST":
        nome = request.form.get('nome_comunidade')
        descricao = request.form.get('descricao')
        max_usuario = request.form.get('max_usuario')
        cursor.execute('SELECT img_perfil FROM tb_comunidade WHERE id_comunidade=%s', (id_comunidade,))
        comunidade_atual = cursor.fetchone()
        imagem_perfil = comunidade_atual['img_perfil']

        if 'imagem_comunidade' in request.files:
            file = request.files['imagem_comunidade']
            if file and file.filename != '' and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                timestamp = str(int(time.time()))
                name, ext = os.path.splitext(filename)
                filename = f"comunidade_{timestamp}{ext}"
                file_path = os.path.join(app.config['UPLOAD_FOLDER_COMUNIDADES'], filename)
                file.save(file_path)
                imagem_perfil = filename
            elif file and file.filename != '':
                flash('Tipo de arquivo não permitido!', 'erro')
                return render_template('editar_comunidade.html')

        sql = "UPDATE tb_comunidade SET nm_comunidade=%s, ds_comunidade=%s, max_usuario=%s, img_perfil=%s WHERE id_comunidade=%s"
        valores = (nome, descricao, max_usuario, imagem_perfil, id_comunidade)

        cursor.execute(sql, valores)
        conexao.commit()
        flash('Comunidade atualizada com sucesso!', 'sucesso')
        return redirect(url_for('gerenciar_comunidades'))
    
    cursor.execute('SELECT * FROM tb_comunidade WHERE id_comunidade=%s',(id_comunidade,))
    comunidades = cursor.fetchone()
    cursor.close()
    conexao.close()

    return render_template('editar_comunidade.html',
                           usuario=session['usuario'],
                           comunidades=comunidades)

@app.route('/editarperfil/<int:id_usuario>', methods=['GET', 'POST'])
def editar_perfil(id_usuario):
    if not session.get('logado'):
        return redirect(url_for('login'))   
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)

    if request.method == "POST":
        nome = request.form.get('nome_usuario')
        biografia = request.form.get('biografia')
        cursor.execute('SELECT img_perfil FROM tb_usuario WHERE id_usuario=%s', (id_usuario,))
        usuario_atual = cursor.fetchone()
        imagem_perfil = usuario_atual['img_perfil']

        if 'imagem_usuario' in request.files:
            file = request.files['imagem_usuario']
            if file and file.filename != '' and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                timestamp = str(int(time.time()))
                name, ext = os.path.splitext(filename)
                filename = f"usuario_{timestamp}{ext}"
                file_path = os.path.join(app.config['UPLOAD_FOLDER_USUARIOS'], filename)
                file.save(file_path)
                imagem_perfil = filename
            elif file and file.filename != '':
                flash('Tipo de arquivo não permitido!', 'erro')
                return render_template('editar_perfil.html')

        sql = "UPDATE tb_usuario SET nm_login=%s, img_perfil=%s, ds_usuario=%s WHERE id_usuario=%s"
        valores = (nome, imagem_perfil, biografia, id_usuario)

        cursor.execute(sql, valores)
        conexao.commit()

        session['usuario'] = nome  # Atualiza nome se mudou
        session['foto_perfil'] = imagem_perfil  # Atualiza foto de perfil

        flash('Perfil atualizado com sucesso!', 'sucesso')
        return redirect(url_for('perfil', id_usuario=id_usuario))

    cursor.execute('SELECT * FROM tb_usuario WHERE id_usuario=%s',(id_usuario,))
    usuarios = cursor.fetchone()
    cursor.close()
    conexao.close()

    return render_template('editar_perfil.html',
                           usuario=session['usuario'],
                           usuarios=usuarios)

@app.route('/perfil/<int:id_usuario>')
def perfil(id_usuario):
    if not session.get('logado'):
        return redirect(url_for('login'))   
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    cursor.execute('SELECT * FROM tb_usuario WHERE id_usuario=%s',(id_usuario,))
    usuarios = cursor.fetchone()
    cursor.execute('''
        SELECT COUNT(*) as total_comunidades_criadas 
        FROM tb_comunidade 
        WHERE criado_por = %s
    ''', (id_usuario,))
    stats = cursor.fetchone()
    cursor.execute('''
        SELECT COUNT(DISTINCT id_comunidade) as total_comunidades_participantes
        FROM tb_usuario_comunidade 
        WHERE id_usuario = %s
    ''', (id_usuario,))
    participantes_stats = cursor.fetchone()

    cursor.close()
    conexao.close()
    return render_template('perfil.html',
                           usuario=session['usuario'],
                           usuarios=usuarios,
                           stats = stats,
                           participantes_stats = participantes_stats)

@app.route('/logout')
def logout():
    session.clear()
    flash('Você saiu do sistema.', 'info')
    return redirect(url_for('login'))

@app.route('/amigos')
def listar_amigos():
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    # Buscar amigos aceitos
    cursor.execute("""
        SELECT 
            u.id_usuario,
            u.nm_login,
            u.img_perfil,
            u.ds_usuario,
            a.data_aceitacao
        FROM tb_amizade a
        INNER JOIN tb_usuario u ON 
            (u.id_usuario = a.id_usuario2 AND a.id_usuario1 = %s) OR
            (u.id_usuario = a.id_usuario1 AND a.id_usuario2 = %s)
        WHERE a.status = 'aceito'
        ORDER BY a.data_aceitacao DESC
    """, (session['id'], session['id']))
    
    amigos = cursor.fetchall()
    
    # Buscar solicitações pendentes recebidas
    cursor.execute("""
        SELECT 
            u.id_usuario,
            u.nm_login,
            u.img_perfil,
            a.data_solicitacao
        FROM tb_amizade a
        INNER JOIN tb_usuario u ON u.id_usuario = a.id_usuario1
        WHERE a.id_usuario2 = %s AND a.status = 'pendente'
        ORDER BY a.data_solicitacao DESC
    """, (session['id'],))
    
    solicitacoes = cursor.fetchall()
    
    cursor.close()
    conexao.close()
    
    return render_template('amigos.html',
                         usuario=session['usuario'],
                         amigos=amigos,
                         solicitacoes=solicitacoes)

@app.route('/enviar_solicitacao/<int:id_amigo>')
def enviar_solicitacao(id_amigo):
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    if id_amigo == session['id']:
        flash('Você não pode enviar solicitação para si mesmo.', 'erro')
        return redirect(url_for('listar_amigos'))
    
    conexao = conectar()
    cursor = conexao.cursor()
    
    try:
        # Garantir que id_usuario1 seja sempre o menor
        id1 = min(session['id'], id_amigo)
        id2 = max(session['id'], id_amigo)
        
        cursor.execute("""
            INSERT INTO tb_amizade (id_usuario1, id_usuario2, status)
            VALUES (%s, %s, 'pendente')
            ON DUPLICATE KEY UPDATE status = 'pendente'
        """, (id1, id2))
        
        conexao.commit()
        flash('Solicitação de amizade enviada!', 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash('Erro ao enviar solicitação.', 'erro')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('listar_amigos'))

@app.route('/aceitar_amizade/<int:id_amigo>')
def aceitar_amizade(id_amigo):
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    conexao = conectar()
    cursor = conexao.cursor()
    
    try:
        id1 = min(session['id'], id_amigo)
        id2 = max(session['id'], id_amigo)
        
        cursor.execute("""
            UPDATE tb_amizade 
            SET status = 'aceito', data_aceitacao = CURRENT_TIMESTAMP
            WHERE id_usuario1 = %s AND id_usuario2 = %s
        """, (id1, id2))
        
        conexao.commit()
        flash('Solicitação de amizade aceita!', 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash('Erro ao aceitar solicitação.', 'erro')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('listar_amigos'))

@app.route('/recusar_amizade/<int:id_amigo>')
def recusar_amizade(id_amigo):
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    conexao = conectar()
    cursor = conexao.cursor()
    
    try:
        id1 = min(session['id'], id_amigo)
        id2 = max(session['id'], id_amigo)
        
        cursor.execute("""
            DELETE FROM tb_amizade 
            WHERE id_usuario1 = %s AND id_usuario2 = %s
        """, (id1, id2))
        
        conexao.commit()
        flash('Solicitação de amizade recusada.', 'info')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash('Erro ao recusar solicitação.', 'erro')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('listar_amigos'))

@app.route('/remover_amigo/<int:id_amigo>')
def remover_amigo(id_amigo):
    if not session.get('logado'):
        return redirect(url_for('login'))
    
    conexao = conectar()
    cursor = conexao.cursor()
    
    try:
        id1 = min(session['id'], id_amigo)
        id2 = max(session['id'], id_amigo)
        
        cursor.execute("""
            DELETE FROM tb_amizade 
            WHERE id_usuario1 = %s AND id_usuario2 = %s
        """, (id1, id2))
        
        conexao.commit()
        flash('Amigo removido da sua lista.', 'info')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash('Erro ao remover amigo.', 'erro')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('listar_amigos'))

@app.route('/index')
def index():
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)

    pesquisa = request.args.get('search', '')
    id_usuario = session.get('id')

    sql = """
        SELECT 
            c.id_comunidade,
            c.nm_comunidade,
            c.ds_comunidade,
            c.max_usuario,
            c.img_perfil,
            u.nm_login as nome_criador,
            u.id_usuario as id_criador,
            COUNT(uc.id_usuario) as total_membros,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM tb_usuario_comunidade uc2 
                    WHERE uc2.id_comunidade = c.id_comunidade 
                    AND uc2.id_usuario = %s
                ) THEN 1 
                ELSE 0 
            END as id_usuario_participante
        FROM tb_comunidade c
        INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
        LEFT JOIN tb_usuario_comunidade uc ON c.id_comunidade = uc.id_comunidade
    """
    params = [id_usuario]

    if pesquisa:
        sql += "WHERE c.nm_comunidade LIKE %s OR c.ds_comunidade LIKE %s"
        patern_pesquisa = f"%{pesquisa}%"
        params.append(patern_pesquisa)
        params.append(patern_pesquisa)
    
    sql +=  """
        GROUP BY c.id_comunidade, c.nm_comunidade, u.nm_login, u.id_usuario
        ORDER BY c.id_comunidade DESC
    """

    cursor.execute(sql, params)
    comunidades = cursor.fetchall()
    cursor.close()
    conexao.close()

    return render_template('index.html', 
                        usuario=session['usuario'], 
                        comunidades=comunidades,
                        pesquisa=pesquisa)

@app.route('/indexadm')
def admin_index():
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    # Estatísticas para o dashboard
    cursor.execute("SELECT COUNT(*) as total FROM tb_usuario")
    total_usuarios = cursor.fetchone()['total']
    
    cursor.execute("SELECT COUNT(*) as total FROM tb_comunidade")
    total_comunidades = cursor.fetchone()['total']
    
    cursor.execute("""
        SELECT COUNT(DISTINCT criado_por) as total 
        FROM tb_comunidade 
        WHERE criado_por IS NOT NULL
    """)
    usuarios_com_comunidades = cursor.fetchone()['total']
    
    cursor.close()
    conexao.close()
    
    return render_template('admin_index.html', 
                         usuario=session['usuario'],
                         total_usuarios=total_usuarios,
                         total_comunidades=total_comunidades,
                         usuarios_com_comunidades=usuarios_com_comunidades,
                         relatorio="Ativo")

# NOVAS ROTAS ADMINISTRATIVAS

@app.route('/adminusuarios')
def admin_listar_usuarios():
    """Listar todos os usuários para administração"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    pesquisa = request.args.get('search', '')
    
    # Buscar estatísticas totais
    cursor.execute("SELECT COUNT(*) as total FROM tb_usuario")
    total_usuarios = cursor.fetchone()['total']
    
    cursor.execute("SELECT COUNT(*) as total FROM tb_comunidade")
    total_comunidades = cursor.fetchone()['total']
    
    cursor.execute("""
        SELECT COUNT(DISTINCT criado_por) as total 
        FROM tb_comunidade 
        WHERE criado_por IS NOT NULL
    """)
    usuarios_com_comunidades = cursor.fetchone()['total']
    
    # Buscar usuários
    sql = """
        SELECT 
            u.id_usuario,
            u.nm_login,
            u.nm_email,
            u.ds_senha,
            u.nr_numero,
            u.dt_cadastro,
            u.img_perfil,
            u.ds_usuario,
            COUNT(DISTINCT c.id_comunidade) as total_comunidades
        FROM tb_usuario u
        LEFT JOIN tb_comunidade c ON u.id_usuario = c.criado_por
    """
    
    params = []
    where_clauses = []
    
    if pesquisa:
        where_clauses.append("(u.nm_login LIKE %s OR u.nm_email LIKE %s)")
        padrao_pesquisa = f"%{pesquisa}%"
        params.extend([padrao_pesquisa, padrao_pesquisa])
    
    if where_clauses:
        sql += " WHERE " + " AND ".join(where_clauses)
    
    sql += """
        GROUP BY u.id_usuario, u.nm_login, u.nm_email, u.nr_numero, u.dt_cadastro
        ORDER BY u.dt_cadastro DESC
    """
    
    cursor.execute(sql, params)
    usuarios = cursor.fetchall()
    
    cursor.close()
    conexao.close()
    
    return render_template('admin_usuarios.html', 
                         usuario=session['usuario'],
                         usuarios=usuarios,
                         pesquisa=pesquisa,
                         total_usuarios=total_usuarios,
                         total_comunidades=total_comunidades,
                         usuarios_com_comunidades=usuarios_com_comunidades)

@app.route('/adminexcluirusuario/<int:id_usuario>')
def admin_excluir_usuario(id_usuario):
    """Excluir um usuário e todas as suas comunidades"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    if id_usuario == session.get('id'):
        flash('Você não pode excluir a si mesmo.', 'erro')
        return redirect(url_for('admin_listar_usuarios'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # 1. Primeiro, buscar o nome do usuário para a mensagem
        cursor.execute('SELECT nm_login FROM tb_usuario WHERE id_usuario = %s', (id_usuario,))
        usuario = cursor.fetchone()
        
        if not usuario:
            flash('Usuário não encontrado.', 'erro')
            return redirect(url_for('admin_listar_usuarios'))
        
        nome_usuario = usuario['nm_login']
        
        # 2. Contar quantas comunidades o usuário criou
        cursor.execute('SELECT COUNT(*) as total FROM tb_comunidade WHERE criado_por = %s', (id_usuario,))
        resultado = cursor.fetchone()
        total_comunidades = resultado['total']
        
        # 3. Excluir os registros de usuários nas comunidades criadas por este usuário
        cursor.execute('''
            DELETE uc FROM tb_usuario_comunidade uc
            INNER JOIN tb_comunidade c ON uc.id_comunidade = c.id_comunidade
            WHERE c.criado_por = %s
        ''', (id_usuario,))
        
        # 4. Excluir as comunidades criadas pelo usuário
        cursor.execute('DELETE FROM tb_comunidade WHERE criado_por = %s', (id_usuario,))
        
        # 5. Remover o usuário de todas as outras comunidades que ele participa
        cursor.execute('DELETE FROM tb_usuario_comunidade WHERE id_usuario = %s', (id_usuario,))
        
        # 6. Finalmente, excluir o usuário
        cursor.execute('DELETE FROM tb_usuario WHERE id_usuario = %s', (id_usuario,))
        
        conexao.commit()
        
        # Mensagem informativa
        mensagem = f'Usuário "{nome_usuario}" excluído com sucesso!'
        if total_comunidades > 0:
            mensagem += f' {total_comunidades} comunidade(s) criada(s) pelo usuário também foram excluída(s).'
        
        flash(mensagem, 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash(f'Erro ao excluir usuário: {str(e)}', 'erro')
        print(f'Erro ao excluir usuário: {e}')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('admin_listar_usuarios'))

@app.route('/admincomunidades')
def admin_listar_comunidades():
    """Listar todas as comunidades para administração"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    # Parâmetros de busca e filtros
    pesquisa = request.args.get('search', '')
    filtro = request.args.get('filtro', 'todas')
    ordenar = request.args.get('ordenar', 'recentes')
    pagina = request.args.get('page', 1, type=int)
    itens_por_pagina = 10
    
    # Construir query base
    sql = """
        SELECT 
            c.id_comunidade,
            c.nm_comunidade,
            c.ds_comunidade,
            c.criado_por,
            c.max_usuario,
            c.img_perfil,
            c.dt_criacao,
            u.nm_login as nome_criador,
            u.img_perfil as criador_foto,
            COUNT(uc.id_usuario) as total_membros
        FROM tb_comunidade c
        INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
        LEFT JOIN tb_usuario_comunidade uc ON c.id_comunidade = uc.id_comunidade
    """
    
    where_clauses = []
    params = []
    
    # Filtro por busca
    if pesquisa:
        where_clauses.append("(c.nm_comunidade LIKE %s OR c.ds_comunidade LIKE %s OR u.nm_login LIKE %s)")
        termo_pesquisa = f"%{pesquisa}%"
        params.extend([termo_pesquisa, termo_pesquisa, termo_pesquisa])
    
    # Filtro por status
    if filtro == 'ativas':
        where_clauses.append("(SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) >= 10")
    elif filtro == 'cheias':
        where_clauses.append("c.max_usuario > 0 AND (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) >= c.max_usuario")
    elif filtro == 'vazias':
        where_clauses.append("(SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) = 0")
    
    # Aplicar WHERE se houver filtros
    if where_clauses:
        sql += " WHERE " + " AND ".join(where_clauses)
    
    # Agrupar por comunidade
    sql += " GROUP BY c.id_comunidade, c.nm_comunidade, u.nm_login"
    
    # Ordenação
    if ordenar == 'antigas':
        sql += " ORDER BY c.dt_criacao ASC"
    elif ordenar == 'membros':
        sql += " ORDER BY total_membros DESC"
    elif ordenar == 'nome':
        sql += " ORDER BY c.nm_comunidade ASC"
    else:  # recentes (padrão)
        sql += " ORDER BY c.dt_criacao DESC"
    
    # Paginação
    sql += " LIMIT %s OFFSET %s"
    offset = (pagina - 1) * itens_por_pagina
    params.extend([itens_por_pagina, offset])
    
    # Executar query
    cursor.execute(sql, params)
    comunidades = cursor.fetchall()
    
    # Contar total para paginação
    count_sql = """
        SELECT COUNT(DISTINCT c.id_comunidade) as total
        FROM tb_comunidade c
        INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
        LEFT JOIN tb_usuario_comunidade uc ON c.id_comunidade = uc.id_comunidade
    """
    count_params = []
    
    count_where_clauses = []
    if pesquisa:
        count_where_clauses.append("(c.nm_comunidade LIKE %s OR c.ds_comunidade LIKE %s OR u.nm_login LIKE %s)")
        termo_pesquisa = f"%{pesquisa}%"
        count_params.extend([termo_pesquisa, termo_pesquisa, termo_pesquisa])
    
    if filtro == 'ativas':
        count_where_clauses.append("(SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) >= 10")
    elif filtro == 'cheias':
        count_where_clauses.append("c.max_usuario > 0 AND (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) >= c.max_usuario")
    elif filtro == 'vazias':
        count_where_clauses.append("(SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) = 0")
    
    if count_where_clauses:
        count_sql += " WHERE " + " AND ".join(count_where_clauses)
    
    cursor.execute(count_sql, count_params)
    total_comunidades = cursor.fetchone()['total']
    
    # Calcular estatísticas
    cursor.execute("""
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) >= 10 THEN 1 END) as ativas,
            COUNT(CASE WHEN c.max_usuario > 0 AND (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) >= c.max_usuario THEN 1 END) as cheias,
            COUNT(CASE WHEN (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) = 0 THEN 1 END) as vazias,
            COALESCE(SUM((SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade)), 0) as total_membros
        FROM tb_comunidade c
    """)
    stats = cursor.fetchone()
    
    # Calcular média de membros
    media_membros = 0
    if stats['total'] > 0:
        media_membros = round(stats['total_membros'] / stats['total'], 1)
    
    cursor.close()
    conexao.close()
    
    # Calcular páginas
    paginas = (total_comunidades + itens_por_pagina - 1) // itens_por_pagina
    
    return render_template('admin_listar_comunidade.html',
                         usuario=session['usuario'],
                         comunidades=comunidades,
                         pesquisa=pesquisa,
                         filtro=filtro,
                         ordenar=ordenar,
                         pagina=pagina,
                         paginas=paginas,
                         total_comunidades=stats['total'],
                         comunidades_ativas=stats['ativas'],
                         total_membros=stats['total_membros'],
                         media_membros=media_membros)


@app.route('/adminexcluircomunidade/<int:id_comunidade>')
def admin_excluir_comunidade(id_comunidade):
    """Excluir uma comunidade como administrador"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # Primeiro, buscar informações da comunidade para mensagem
        cursor.execute('''
            SELECT 
                c.nm_comunidade,
                COUNT(uc.id_usuario) as total_membros,
                u.nm_login as nome_criador
            FROM tb_comunidade c
            LEFT JOIN tb_usuario_comunidade uc ON c.id_comunidade = uc.id_comunidade
            INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
            WHERE c.id_comunidade = %s
            GROUP BY c.id_comunidade
        ''', (id_comunidade,))
        
        comunidade = cursor.fetchone()
        
        if not comunidade:
            flash('Comunidade não encontrada.', 'erro')
            return redirect(url_for('admin_listar_comunidades'))
        
        nome_comunidade = comunidade['nm_comunidade']
        total_membros = comunidade['total_membros']
        nome_criador = comunidade['nome_criador']
        
        # Excluir primeiro os membros da comunidade
        cursor.execute('DELETE FROM tb_usuario_comunidade WHERE id_comunidade = %s', (id_comunidade,))
        
        # Excluir a comunidade
        cursor.execute('DELETE FROM tb_comunidade WHERE id_comunidade = %s', (id_comunidade,))
        
        conexao.commit()
        
        # Mensagem informativa
        mensagem = f'Comunidade "{nome_comunidade}" excluída com sucesso!'
        if total_membros > 0:
            mensagem += f' {total_membros} membro(s) removido(s).'
        
        flash(mensagem, 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash(f'Erro ao excluir comunidade: {str(e)}', 'erro')
        print(f'Erro ao excluir comunidade: {e}')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('admin_listar_comunidades'))

@app.route('/cadastroadmin', methods=['GET', 'POST'])
def cadastro_admin():
    if request.method == 'POST':
        nome_login = request.form.get('nome_login')
        email = request.form.get('email')
        senha = request.form.get('senha')
        # telefone = request.form.get('telefone')
        # foto_perfil = 'perfilplaceholder.png'

        if 'foto_perfil' in request.files:
            file = request.files['foto_perfil']
            if file and file.filename != '' and allowed_file(file.filename):
                filename = secure_filename(file.filename)
                import time
                timestamp = str(int(time.time()))
                name, ext = os.path.splitext(filename)
                filename = f"usuario_{timestamp}{ext}"
                file_path = os.path.join(app.config['UPLOAD_FOLDER_USUARIOS'], filename)
                file.save(file_path)
                foto_perfil = filename
            elif file and file.filename != '':
                flash('Tipo de arquivo não permitido!', 'erro')
                return render_template('cadastrar_admin.html')
            
        if nome_login and email and senha:
            conexao = conectar()
            cursor = conexao.cursor()
            try:
                sql = 'INSERT INTO tb_admin(nm_login, nm_email,ds_senha, dt_cadastro) VALUES (%s, %s,%s,%s)'
                valores = (nome_login , email, senha, datetime.now())
                cursor.execute(sql, valores)
                conexao.commit()
                cursor.close()
                conexao.close()
                flash('Admin cadastrado com sucesso!', 'sucesso')
                return redirect(url_for('admin_index'))
            
            except mysql.connector.Error as e:
                conexao.rollback()
                cursor.close()
                conexao.close()

                if e.errno == 1062:
                    flash('Nome de usuário, e-mail ou telefone já está em uso.', 'erro')
                    return render_template('cadastrar_admin.html')
    return render_template('cadastrar_admin.html')

@app.route('/listaradmin')
def admin_listar_admin():
    """Listar todos os usuários para administração"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    pesquisa = request.args.get('search', '')
    
    # Buscar estatísticas totais
    cursor.execute("SELECT COUNT(*) as total FROM tb_admin")
    total_admins = cursor.fetchone()['total']
    
    # Buscar usuários
    sql = """
        SELECT 
            a.id_admin,
            a.nm_login,
            a.nm_email,
            a.ds_senha
        FROM tb_admin a
    """
    
    params = []
    where_clauses = []
    
    if pesquisa:
        where_clauses.append("(a.nm_login LIKE %s OR a.nm_email LIKE %s)")
        padrao_pesquisa = f"%{pesquisa}%"
        params.extend([padrao_pesquisa, padrao_pesquisa])
    
    if where_clauses:
        sql += " WHERE " + " AND ".join(where_clauses)
    
    sql += """
        GROUP BY a.id_admin, a.nm_login, a.nm_email
    """
    #, u.dt_cadastro ORDER BY u.dt_cadastro DESC adicionar depois dt_cadastro na tabela admin
    cursor.execute(sql, params)
    admins = cursor.fetchall()
    
    cursor.close()
    conexao.close()
    
    return render_template('admin_listar_admin.html', 
                         usuario=session['usuario'],
                         admins=admins,
                         pesquisa=pesquisa,
                         total_usuarios=total_admins)

@app.route('/adminexcluiradmin/<int:id_admin>')
def excluir_admin(id_admin):
    """Excluir um usuário e todas as suas comunidades"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    if id_admin == session.get('id'):
        flash('Você não pode excluir a si mesmo.', 'erro')
        return redirect(url_for('admin_listar_admin'))
    
    if id_admin == session.get('id') or id_admin == session.get('idadm'):
        flash('Você não pode excluir a si mesmo.', 'erro')
        return redirect(url_for('admin_listar_admin'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    try:
        # 1. Primeiro, buscar o nome do usuário para a mensagem
        cursor.execute('SELECT nm_login FROM tb_admin WHERE id_admin = %s', (id_admin,))
        usuario = cursor.fetchone()
        
        if not usuario:
            flash('Admin não encontrado.', 'erro')
            return redirect(url_for('admin_listar_admin'))
        
        nome_usuario = usuario['nm_login']
        
        # 6. Finalmente, excluir o usuário
        cursor.execute('DELETE FROM tb_admin WHERE id_admin = %s', (id_admin,))
        
        conexao.commit()
        
        # Mensagem informativa
        mensagem = f'Admin "{nome_usuario}" excluído com sucesso!'
        
        flash(mensagem, 'sucesso')
        
    except mysql.connector.Error as e:
        conexao.rollback()
        flash(f'Erro ao excluir usuário: {str(e)}', 'erro')
        print(f'Erro ao excluir usuário: {e}')
    
    finally:
        cursor.close()
        conexao.close()
    
    return redirect(url_for('admin_listar_admin'))

@app.route('/admincomunidades/<int:id_comunidade>/membros')
def admin_listar_membros_comunidade(id_comunidade):
    """Listar membros de uma comunidade específica"""
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    # Buscar informações da comunidade
    cursor.execute('''
        SELECT 
            c.nm_comunidade,
            c.ds_comunidade,
            c.img_perfil,
            u.nm_login as nome_criador
        FROM tb_comunidade c
        INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
        WHERE c.id_comunidade = %s
    ''', (id_comunidade,))
    
    comunidade = cursor.fetchone()
    
    if not comunidade:
        flash('Comunidade não encontrada.', 'erro')
        return redirect(url_for('admin_listar_comunidades'))
    
    # Buscar membros da comunidade
    cursor.execute('''
        SELECT 
            u.id_usuario,
            u.nm_login,
            u.nm_email,
            u.img_perfil,
            u.dt_cadastro,
            uc.data_entrada
        FROM tb_usuario_comunidade uc
        INNER JOIN tb_usuario u ON uc.id_usuario = u.id_usuario
        WHERE uc.id_comunidade = %s
        ORDER BY uc.data_entrada DESC
    ''', (id_comunidade,))
    
    membros = cursor.fetchall()
    
    cursor.close()
    conexao.close()
    
    return render_template('admin_membros_comunidade.html',
                         usuario=session['usuario'],
                         comunidade=comunidade,
                         membros=membros,
                         total_membros=len(membros))

@app.route('/adminrelatorios')
def admin_relatorios():
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))
    
    return render_template('admin_relatorios.html', 
                         usuario=session['usuario'])

@app.route('/adminconfigs')
def admin_configs():
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') != 'admin':
        return redirect(url_for('index'))

    return render_template('admin_configs.html',
                           usuario = session['usuario'])

@app.route("/dados_usuario")
def dados_usuario():    
    if not session.get('logado'):
        return redirect(url_for('login'))
    if session.get('tipo_usuario') == 'admin':
        return redirect(url_for('admin_index'))
    conexao = conectar()
    cursor = conexao.cursor(dictionary=True)
    
    return render_template("dados_usuario.html")
    
if __name__ == '__main__':
    app.run(debug=True)