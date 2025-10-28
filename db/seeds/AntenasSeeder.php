<?php
use Phinx\Seed\AbstractSeed;

class AntenasSeeder extends AbstractSeed
{
    private const CHUNK = 5000;

    public function run(): void
    {
        $pdo = $this->getAdapter()->getConnection();

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        try {
            //Carga antenas
            $total = 100000;
            $table = $this->table('antenas');
            $batch = [];
            $now = date('Y-m-d H:i:s');

            for ($i = 1; $i <= $total; $i++) {
                $batch[] = [
                    'descricao'         => sprintf('ANT-%06d', $i),
                    'latitude'          => $this->gerarLatitude(),
                    'longitude'         => $this->gerarLongitude(),
                    'uf'            => $this->randUf(),
                    'altura'            => $this->randAltura(),
                    'data_implantacao'  => $this->randDataImplantacao(),
                    'foto_path'              => '/uploads/fotos_antenas/antena.png',
                ];

                if (count($batch) === self::CHUNK) {
                    $table->insert($batch)->saveData();
                    $batch = [];
                }
            }

            if ($batch) {
                $table->insert($batch)->saveData();
            }

            //Carga estados
            $data = [
                ['uf' => 'AC', 'uf_descricao' => 'Acre'],
                ['uf' => 'AL', 'uf_descricao' => 'Alagoas'],
                ['uf' => 'AM', 'uf_descricao' => 'Amazonas'],
                ['uf' => 'AP', 'uf_descricao' => 'Amapá'],
                ['uf' => 'BA', 'uf_descricao' => 'Bahia'],
                ['uf' => 'CE', 'uf_descricao' => 'Ceará'],
                ['uf' => 'DF', 'uf_descricao' => 'Distrito Federal'],
                ['uf' => 'ES', 'uf_descricao' => 'Espírito Santo'],
                ['uf' => 'GO', 'uf_descricao' => 'Goiás'],
                ['uf' => 'MA', 'uf_descricao' => 'Maranhão'],
                ['uf' => 'MG', 'uf_descricao' => 'Minas Gerais'],
                ['uf' => 'MS', 'uf_descricao' => 'Mato Grosso do Sul'],
                ['uf' => 'MT', 'uf_descricao' => 'Mato Grosso'],
                ['uf' => 'PA', 'uf_descricao' => 'Pará'],
                ['uf' => 'PB', 'uf_descricao' => 'Paraíba'],
                ['uf' => 'PE', 'uf_descricao' => 'Pernambuco'],
                ['uf' => 'PI', 'uf_descricao' => 'Piauí'],
                ['uf' => 'PR', 'uf_descricao' => 'Paraná'],
                ['uf' => 'RJ', 'uf_descricao' => 'Rio de Janeiro'],
                ['uf' => 'RN', 'uf_descricao' => 'Rio Grande do Norte'],
                ['uf' => 'RO', 'uf_descricao' => 'Rondônia'],
                ['uf' => 'RR', 'uf_descricao' => 'Roraima'],
                ['uf' => 'RS', 'uf_descricao' => 'Rio Grande do Sul'],
                ['uf' => 'SC', 'uf_descricao' => 'Santa Catarina'],
                ['uf' => 'SE', 'uf_descricao' => 'Sergipe'],
                ['uf' => 'SP', 'uf_descricao' => 'São Paulo'],
                ['uf' => 'TO', 'uf_descricao' => 'Tocantins'],
            ];

            $this->table('estados')->insert($data)->saveData();
        } finally {
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function randFloat(float $min, float $max): float
    {
        return $min + lcg_value() * ($max - $min);
    }

    function gerarLatitude(): float
    {
        return round(mt_rand(-33750000, 5270000) / 1000000, 6);
    }

    function gerarLongitude(): float
    {
        return round(mt_rand(-73980000, -34790000) / 1000000, 6);
    }
    private function randUf(): string
    {
        static $ufs = [
            'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA',
            'MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN',
            'RS','RO','RR','SC','SP','SE','TO'
        ];

        return $ufs[array_rand($ufs)];
    }

    private function randAltura(): float
    {
        //entre 3 e 20 metros
        return round($this->randFloat(3, 20), 2);
    }

    private function randDataImplantacao(): ?string
    {
        //porcentagem de nulls
        if (mt_rand(1, 100) <= 30) {
            return null;
        }

        // Caso contrário, gera uma data aleatória dos últimos 3 ano
        $timestamp = strtotime('-' . mt_rand(0, 1095) . ' days');
        return date('Y-m-d', $timestamp);
    }

}
