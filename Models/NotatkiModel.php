<?php
namespace App\Models;

use CodeIgniter\Model;

class NotatkiModel extends Model
{
    protected $table      = 'notatki';
    protected $primaryKey = 'id';
    protected $useTimestamps  = false;
    protected $allowedFields  = ['tresc', 'opublikowana', 'TurniejID', 'KlubID', 'wyklucz_KlubID'];

    public function getLatestPublished(int $turniejID, ?int $klubID, int $limit = 10): array
    {
        $builder = $this->where('opublikowana', 1)
                        ->where('TurniejID', $turniejID)
                        ->groupStart()
                            ->where('KlubID IS NULL', null, false)
                            ->orWhere('KlubID', $klubID)
                        ->groupEnd()
                        ->orderBy('created_at', 'DESC');
                        
        if ($klubID !== null) {
            $builder->where('(wyklucz_KlubID IS NULL OR wyklucz_KlubID != ' . (int)$klubID . ')');
}
 

        return $builder->findAll($limit);
    }

    public function getForAdmin(int $turniejID, int $limit = 20): array
    {
        return $this->where('TurniejID', $turniejID)
                    ->orderBy('created_at', 'DESC')
                    ->findAll($limit);
    }

    public function addNotatka(array $data): bool|int
    {
        return $this->insert($data);
    }

    public function ukryj(int $id): bool
    {
        return $this->update($id, ['opublikowana' => 0]);
    }
}
