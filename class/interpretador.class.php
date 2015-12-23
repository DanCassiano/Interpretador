<?php 

	/**
	* Interpretador
	*/
	class Interpretador
	{
		
		private $arquivos;

		private $ponteiro = 0;


		private $dadosDeFuncoes;

		private $query;

		private $variaveis;

		private $campos;

		private $render = array(
				'input'				=> "<input type=\"text\" value=\"{valor}\" >",
				'input[checkbox]'	=> "<input type=\"checkbox\" value=\"{valor}\" >{valor}</input>",
				'input[radio]'		=> "<input type=\"radio\" value=\"{valor}\" >{valor}</input>",
				'select'			=> "<select name=\"\" id=\"\">{valor}</select>",
				'textarea'			=> "<textarea name=\"\" id=\"\" cols=\"30\" rows=\"10\">{valor}</textarea>"
			);

		function __construct( ){ }

		private function getArquivo( ) {
			return file( $this->arquivos[ $this->ponteiro ] );
		}

		private function isComentario( $dado ) {
			return preg_match("/([\/\*]+)|([\*\/]+)/", $dado);
		}

		private function isFecha($dado) {
			return preg_match("/([*][\/]+)/", $dado);
		}

		private function isAber( $dado ) {
			return preg_match("/([\/][*]+)/", $dado);
		}

		private function notAber( $dado ) {
			return preg_match("/(^[\/][*]+)/", $dado);
		}

		private function extraindoDados() {

			$query = "";
			$dados = $this->getArquivo( );
			$cont = 0;
			array_filter( $this->dadosDeFuncoes, 
				function($d) use ( &$query, $dados, &$cont ) {

					foreach ( explode( ";", $d ) as $i => $v ) {

						if( 	!$this->isAber( $dados[ $v ] ) 
							&& 	!$this->isFecha( $dados[ $v ] ) ) 
						{
							if( empty($query[ $cont ]) )
								$query[ $cont ] = "";

							$query[ $cont ] .= $dados[ $v ] ;
						}

						if( $this->isFecha( $dados[ $v ] ) ) {
							$cont++;
							$var = explode("=", $dados[ $v +1] );
							$this->variaveis[][ @trim($var[0]) ] = @trim($var[1]);
						}
					}
				return true;
			});
			$this->query = $query;
		}

		private function trataQuery() {

			if( $this->query ) {

				foreach ($this->query as $i => $d ) {
					
					foreach (explode("\n", $d) as $j => $v) {
						if( $v != "" ) {

							$f = explode(":", $v );
							$dado[ str_replace(array("*","\t"," "), "" , @trim( strtolower( $f[0] ) )) ] = @trim($f[1]);
							$this->campos[ $i ] = $dado;
						}
					}
				}
			}
		}

		private function render() {

			$form = "<form action=\"\">";

			foreach ($this->campos as $i => $v) {

				$titulo = "";
				$valor = "";

				if( $v['tipo'] == 'select' ) {
					foreach (explode(";",$v['op']) as $k => $d) {
						$g = explode("=", $d );
						$valor .=  "<option value='{$g[0]}' >{$g[1]}</option>";
					}
				}
				else
					$valor = $this->variaveis[ $i ][ array_keys( $this->variaveis[ $i ] )[0] ];


				if( !empty( $v['titulo'] ) )
					$titulo = $v['titulo'];

				$form .= "<div class=\"row\">" . 
							( $v['tipo'] != "input[checkbox]" ? ( $v['tipo'] != "input[radio]" ? "<label for=\"\">{$titulo}</label><br/>" : "" ) : "" ) .
							str_replace("{valor}", $valor ,$this->render[ $v['tipo'] ] ) . 
						"</div>";
			}

			echo $form."</form>";
		}

		public function setArq( $arquivo ) {
			$this->arquivos[] = $arquivo;
		}

		public function getFuncoes() {
			// echo "<pre>";
			$funcoes = array();
			$cont = 0;
			foreach ($this->getArquivo( ) as $lineNum => $line) {

				if( $line != "" ) {

					if( $this->isComentario( $line ) ) {
						if( empty( $funcoes[$cont] ) )
							$funcoes[$cont] = "";

						$funcoes[$cont] .= $lineNum.";";
					}

					if( $this->isFecha( $line ) ) {
						$funcoes[$cont] = substr( $funcoes[$cont], 0 , -1 );
						$cont++;
					}

					// echo "LINHA {$lineNum}  => " .( $this->isComentario( $line ) == 1 ? "<strong>Comentario</strong>" : "" ). " {$line} <br/> ";
				}
			}
			
			$this->dadosDeFuncoes = $funcoes;
			$this->extraindoDados();
			$this->trataQuery();
			$this->render();
		}



	}