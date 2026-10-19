<?php

use App\Models\Encuesta;
use App\Models\Envios;
use App\Models\Opciones;
use App\Models\Preguntas;
use App\Models\User;

function createSurveyWithQuestion(User $owner): Encuesta
{
    $survey = Encuesta::create([
        'id_usuario' => $owner->id,
        'titulo_encuesta' => 'Encuesta de prueba',
        'descripcion_encuesta' => 'Descripción de prueba',
        'estado_encuesta' => true,
        'anonima' => false,
    ]);

    $question = Preguntas::create([
        'id_encuesta' => $survey->id,
        'pregunta' => '¿Cómo estuvo el servicio?',
        'tipo_pregunta' => 'unica',
        'requerido' => true,
    ]);

    Opciones::create([
        'id_pregunta' => $question->id,
        'opcion' => 'Excelente',
        'opcion_correcta' => false,
    ]);

    return $survey;
}

test('results index links to the working results page for each survey', function () {
    $owner = User::factory()->create(['role' => 'instructor']);
    $survey = createSurveyWithQuestion($owner);

    $response = $this->actingAs($owner)->get(route('resultados.index'));

    $response->assertOk();
    $response->assertSee(route('encuestas.resultados', $survey), false);
});

test('survey results page renders its questions and options', function () {
    $owner = User::factory()->create(['role' => 'instructor']);
    $survey = createSurveyWithQuestion($owner);

    $response = $this->actingAs($owner)->get(route('encuestas.resultados', $survey));

    $response->assertOk();
    $response->assertSee('¿Cómo estuvo el servicio?');
    $response->assertSee('Excelente');
});

test('legacy results route redirects to the survey results page', function () {
    $owner = User::factory()->create(['role' => 'instructor']);
    $survey = createSurveyWithQuestion($owner);

    $this->actingAs($owner)
        ->get(route('resultados.show', $survey))
        ->assertRedirect(route('encuestas.resultados', $survey));
});

test('non-anonymous survey results include respondent details', function () {
    $owner = User::factory()->create(['role' => 'instructor']);
    $respondent = User::factory()->create([
        'name' => 'Participante Visible',
        'email' => 'participante@example.com',
        'role' => 'aprendiz',
    ]);
    $survey = createSurveyWithQuestion($owner);
    Envios::create([
        'id_encuesta' => $survey->id,
        'id_usuario' => $respondent->id,
    ]);

    $response = $this->actingAs($owner)->get(route('encuestas.resultados', $survey));

    $response->assertOk();
    $response->assertSee('Participante Visible');
    $response->assertSee('participante@example.com');
});

test('anonymous survey results never expose respondent details', function () {
    $owner = User::factory()->create(['role' => 'instructor']);
    $respondent = User::factory()->create([
        'name' => 'Participante Oculto',
        'email' => 'oculto@example.com',
        'role' => 'aprendiz',
    ]);
    $survey = createSurveyWithQuestion($owner);
    $survey->update(['anonima' => true]);
    Envios::create([
        'id_encuesta' => $survey->id,
        'id_usuario' => $respondent->id,
    ]);

    $response = $this->actingAs($owner)->get(route('encuestas.resultados', $survey));

    $response->assertOk();
    $response->assertDontSee('Participante Oculto');
    $response->assertDontSee('oculto@example.com');
    $response->assertSee('Encuesta anónima');
});