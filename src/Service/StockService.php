<?php

namespace App\Service;

use App\Entity\InventaireMere;
use App\Repository\InventaireFilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class StockService 
{
    private $entityManager;
    private $inventaireFilleRepository;

    public function __construct(EntityManagerInterface $entityManager, InventaireFilleRepository $inventaireFilleRepository)
    {
        $this->entityManager = $entityManager;
        $this->inventaireFilleRepository = $inventaireFilleRepository;
    }

    
    public function saveIntenvaire(InventaireMere $inventaire) {
        try{
            $this->entityManager->beginTransaction();
            $isSave = $inventaire->getId() === null;
            $inventaire->setStatut(1);
            $this->entityManager->persist($inventaire);
            
            if(!$isSave){
                $filles = $this->inventaireFilleRepository->findValidByMere($inventaire->getId());
                foreach($filles as $fille){
                    $fille->setStatut(0);
                    $this->entityManager->persist($fille);
                }
            }

            $length = 0;
            foreach($inventaire->getInventaireFilles() as $fille){
                $fille->setStatut(1);
                $fille->setMere($inventaire);
                $this->entityManager->persist($fille);
                $length++; 
            }
            if($isSave && $length == 0) throw new Exception("Aucun détail sélectionné");
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch(\Exception $ex){
            if($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $ex;
        } finally {
            $this->entityManager->clear();
        }
    }

    

    public function supprimerInventaire(InventaireMere $inventaire)
    {
        try{
            $this->entityManager->beginTransaction();
            $inventaire->setStatut(0);
            $this->entityManager->persist($inventaire);
            foreach($inventaire->getInventaireFilles() as $fille){
               $fille->setStatut(0);
               $this->entityManager->persist($fille);
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch(\Exception $ex){
            if($this->entityManager->getConnection()->isTransactionActive()) {
                $this->entityManager->rollback();
            }
            throw $ex;
        } finally {
            $this->entityManager->clear();
        }
    }

    
}