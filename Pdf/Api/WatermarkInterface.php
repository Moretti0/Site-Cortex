<?php
declare(strict_types=1);

namespace Cortex\Pdf\Api;

interface WatermarkInterface
{
    /**
     * Adiciona marca d'água ao PDF
     *
     * @param string $pdfContent Conteúdo do PDF
     * @param array $options Opções da marca d'água
     * @return string Conteúdo do PDF com marca d'água
     */
    public function addWatermark(string $pdfContent, array $options = []): string;

    /**
     * Adiciona marca d'água de imagem ao PDF
     *
     * @param string $pdfContent Conteúdo do PDF
     * @param string $imagePath Caminho da imagem
     * @param array $options Opções da marca d'água
     * @return string Conteúdo do PDF com marca d'água
     */
    public function addImageWatermark(string $pdfContent, string $imagePath, array $options = []): string;
} 