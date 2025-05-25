<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PGDI - Plataforma de Gestão Documental Integrada</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="css1/style.css">
    <link rel="stylesheet" href="css1/bootstrap.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        header {
            background-color: #005073;
            color: #fff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        header nav {
            display: flex;
            gap: 20px;
        }
        header nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: color 0.3s;
        }
        header nav a:hover {
            color: #ffdd57;
        }
        header nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: #ffdd57;
            left: 0;
            bottom: -5px;
            transition: width 0.3s;
        }
        header nav a:hover::after {
            width: 100%;
        }
        .hero {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 40px;
            background: #e8f4f8;
        }
        .hero .text {
            max-width: 50%;
        }
        .hero .text h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .hero .text p {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .hero .text .buttons {
            display: flex;
            gap: 20px;
        }
        .hero .text .buttons a {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            color: #fff;
            transition: background-color 0.3s;
        }
        .hero .text .buttons .start {
            background-color: #007bff;
        }
        .hero .text .buttons .start:hover {
            background-color: #0056b3;
        }
        .hero .text .buttons .explore {
            background-color: #28a745;
        }
        .hero .text .buttons .explore:hover {
            background-color: #1e7e34;
        }
        .hero img {
            max-width: 40%;
            border-radius: 10px;
        }
        .features {
            padding: 40px;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .features .feature-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            max-width: 1200px;
            width: 100%;
            margin-bottom: 30px;
        }
        .features .feature-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            flex: 1;
        }
        .features .feature-item img {
            width: 200px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .features .feature-item h3 {
            margin: 10px 0 10px 0;
            font-size: 1.2rem;
        }
        .features .feature-item p {
            margin: 0;
            font-size: 1rem;
            color: #555;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #005073;
            color: #fff;
            margin-top: auto;
        }
        footer .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }
        footer .social-icons a {
            color: #fff;
            font-size: 1.5rem;
            text-decoration: none;
            transition: color 0.3s;
        }
        footer .social-icons a:hover {
            color: #ffdd57;
        }

        #img{
            margin-right: 350px;
            
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">PGDI</div>
        <nav>
            <a href="index.php">Início</a>
            <a href="#">Sobre</a>
            <a href="login.php">Login</a>
        </nav>
    </header>

    <section class="hero">
        <div class="text">
            <h1>Plataforma de Gestão Documental Integrada - PGDI</h1>
            <p>Com a PGDI, sua organização terá uma solução completa para armazenamento, gerenciamento e distribuição de documentos com segurança e eficiência.</p>
        </div>
    </section>
    <br><br><br><br><br><br>

    <div class="appointment_section">
        <div class="container">
           <div class="appointment_box">
              <div class="row">
                 <div class="col-md-12">
                    <h1 class="appointment_taital">O que tens na <span style="color: #0cb7d6;">PGDI</span></h1>
                 </div>
              </div>
              <div class="appointment_section_2">
                 
                 <div class="row">
                    <div class="col-md-4">
                       <p class="doctorname_text">ARMAZENAMENTO</p>
                    </div>
                    <div class="col-md-4">
                       <p class="doctorname_text">GESTÃO</p>
                    </div>
                    <div class="col-md-4">
                       <p class="doctorname_text">SEGURANÇA</p>
                    </div>
                 </div>
              </div>
           </div>
        </div>
     </div>

    <section class="features">
        <div class="feature-row">
            <div class="feature-item">
                <img src="img/pg1.jpg" alt="Imagem 1" >
                <h3>Organização Centralizada</h3>
                <p>Todos os documentos da sua organização em um só lugar, acessíveis e seguros.</p>
            </div>
            <div class="feature-item">
                <img src="img/pg2.jpg" alt="Imagem 2">
                <h3>Fluxos de Trabalho Personalizados</h3>
                <p>Adapte a plataforma às necessidades específicas de cada departamento.</p>
            </div>
            <div class="feature-item">
                <img src="img/pg3.jpg" alt="Imagem 3">
                <h3>Segurança e Conformidade</h3>
                <p>Proteção robusta de dados para garantir confidencialidade e integridade.</p>
            </div>
        </div>
    </section>

    <div class="testimonial_section layout_padding">
        <div class="container">
           <div class="row">
              <div class="col-md-12">
                 <h1 class="testimonial_taital">Acerca dos Departamentos</h1>
              </div>
           </div>
           <div class="customer_section_2">
              <div class="row">
                 <div class="col-md-12">
                     <div class="box_main">
                       <div id="main_slider" class="carousel slide" data-ride="carousel">
                          <div class="carousel-inner">
                             <div class="carousel-item active">
                                <div class="customer_main">
                                   <div class="customer_right">
                                      <h3 class="customer_name">Departamento de Recusros Humanos </h3>
                                      <p class="default_text">RH</p>
                                      <p class="enim_text">Gerencia o recrutamento, seleção, contratação, treinamento, desenvolvimento, e retenção de talentos. É responsável também por folha de pagamento, benefícios, gestão de desempenho e políticas de cultura organizacional.</p>
                                   </div>
                                </div>
                             </div>
                             <div class="carousel-item">
                                <div class="customer_main">
                                   <div class="customer_right">
                                      <h3 class="customer_name">Departamento de Finanças</h3>
                                     <p class="default_text">Finanças</p>
                                      <p class="enim_text">Cuida da gestão financeira, incluindo controle de receitas, despesas, fluxo de caixa, contas a pagar e a receber, e planejamento orçamentário. O departamento financeiro também lida com contabilidade, auditoria e gestão de investimentos.</p>
                                   </div>
                                </div>
                             </div>
                             <div class="carousel-item">
                                <div class="customer_main">
                                   <div class="customer_right">
                                      <h3 class="customer_name">Departamento de TI</h3>
                                      <p class="default_text">TI</p>
                                      <p class="enim_text">Responsável pela infraestrutura tecnológica da empresa, incluindo hardware, software, redes e sistemas de segurança de dados. Dá suporte aos funcionários, gerencia a segurança da informação e implementa soluções tecnológicas para otimizar os processos da empresa.</p>
                                   </div>
                                </div>
                             </div>
                             <div class="carousel-item">
                                <div class="customer_main">
                                   <div class="customer_right">
                                      <h3 class="customer_name">Departamento de Vendas</h3>
                                      <p class="default_text">Vendas</p>
                                      <p class="enim_text">Responsável pela prospecção e captação de novos clientes, negociação e fechamento de vendas, e gestão de relacionamento com os clientes. Esse departamento é crucial para gerar receita e garantir a satisfação do cliente.</p>
                                   </div>
                                </div>
                             </div>
                             <div class="carousel-item">
                                <div class="customer_main">
                                   <div class="customer_right">
                                      <h3 class="customer_name">Departamento de Operações</h3>
                                      <p class="default_text">Operações</p>
                                      <p class="enim_text">Gerencia a produção e execução dos processos operacionais, garantindo a eficiência e qualidade dos produtos e serviços. Em empresas de manufatura, por exemplo, é o departamento que cuida da linha de produção e logística.</p>
                                   </div>
                                </div>
                             </div>
                             <div class="carousel-item">
                                <div class="customer_main">
                                   <div class="customer_right">
                                      <h3 class="customer_name">Departamento de Marketing</h3>
                                      <p class="default_text">Marketing</p>
                                      <p class="enim_text">Desenvolve estratégias para promover a marca, produtos e serviços da empresa. Responsável por publicidade, pesquisa de mercado, campanhas de marketing digital, redes sociais, e comunicação com o público para aumentar a visibilidade e atratividade da empresa.</p>
                                   </div>
                                </div>
                             </div>
                          </div>
                          <a class="carousel-control-prev" href="#main_slider" role="button" data-slide="prev">
                          <i class="fa fa-angle-left"></i>
                          </a>
                          <a class="carousel-control-next" href="#main_slider" role="button" data-slide="next">
                          <i class="fa fa-angle-right"></i>
                          </a>
                       </div>
                    </div>
                    <br>
                     <br>
                    <hr>
                 </div>
               </div>
           </div>
        </div>
     </div>

    <footer>
        &copy; 2025 PGDI - Plataforma de Gestão Documental Integrada. Todos os direitos reservados.
       
    </footer>

    <!-- Javascript files-->
    <script src="js1/jquery.min.js"></script>
    <script src="js1/bootstrap.bundle.min.js"></script>
</body>
</html>
