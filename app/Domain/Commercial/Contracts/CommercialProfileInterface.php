<?php

namespace App\Domain\Commercial\Contracts;

interface CommercialProfileInterface
{
    /**
     * Clave única del rubro macro (ej: 'gastronomia', 'retail', 'oficina', 'salud').
     */
    public function getCategoryKey(): string;

    /**
     * Etiqueta legible del rubro macro (ej: 'Gastronomía').
     */
    public function getCategoryLabel(): string;

    /**
     * Clave única del sub-rubro (ej: 'heladeria_artesanal', 'pizzeria', 'cafeteria').
     */
    public function getSubcategoryKey(): string;

    /**
     * Etiqueta legible del sub-rubro (ej: 'Heladería Artesanal & Fábrica de Frío').
     */
    public function getSubcategoryLabel(): string;

    /**
     * Descripción operativa del sub-rubro.
     */
    public function getDescription(): string;

    /**
     * Categorías de artefactos críticas (24/7 o ininterrumpibles).
     */
    public function getCriticalCategories(): array;

    /**
     * Categorías de proceso intensivo de este sub-rubro.
     */
    public function getProcessCategories(): array;

    /**
     * Multiplicador de carga continua / standby (línea base).
     */
    public function getStandbyMultiplier(): float;

    /**
     * Coeficiente de sensibilidad térmica / climática frente a olas de calor o frío.
     */
    public function getThermalSensitivity(): float;

    /**
     * Coeficiente de impacto por cliente/comensal/visitante.
     */
    public function getVisitorsSocialCoefficient(): float;

    /**
     * Etiqueta de la unidad de visitantes (ej: 'comensales', 'clientes', 'pacientes').
     */
    public function getVisitorsUnitLabel(): string;

    /**
     * Calcula la carga operativa en base al contexto (turnos, visitantes, horarios).
     */
    public function calculateOperationalLoad(array $context): float;

    /**
     * Lista de artefactos típicos sugeridos para onboarding rápido.
     */
    public function getSuggestedAppliances(): array;

    /**
     * Campos personalizados adicionales que requiere este perfil.
     */
    public function getCustomFields(): array;
}
