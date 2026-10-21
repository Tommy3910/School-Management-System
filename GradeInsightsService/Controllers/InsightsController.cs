using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.ML;
using GradeInsightsService.MLModels;

namespace GradeInsightsService.Controllers
{
    [ApiController]
    [Route("api/[controller]")]
    public class InsightsController : ControllerBase
    {
        private readonly PredictionEnginePool<StudentGradeInput, StudentGradePrediction> _predictionEnginePool;

        public InsightsController(PredictionEnginePool<StudentGradeInput, StudentGradePrediction> predictionEnginePool)
        {
            _predictionEnginePool = predictionEnginePool;
        }

        // GET: api/insights/health
        [HttpGet("health")]
        public IActionResult HealthCheck()
        {
            return Ok(new
            {
                status = "online",
                timestamp = DateTime.UtcNow,
                service = "GradeInsightsService (ML.NET)"
            });
        }

        // POST: api/insights/predict
        [HttpPost("predict")]
        [ProducesResponseType(typeof(StudentGradePrediction), StatusCodes.Status200OK)]
        [ProducesResponseType(StatusCodes.Status400BadRequest)]
        public ActionResult<StudentGradePrediction> PredictGrade([FromBody] StudentGradeInput input)
        {
            if (!ModelState.IsValid)
            {
                return BadRequest(ModelState);
            }

            // Run ML inference using the pooled model engine
            StudentGradePrediction prediction = _predictionEnginePool.Predict(
                modelName: "GradePredictionModel", 
                example: input
            );

            // Clamp prediction within 0 to 100 range for realistic grading
            prediction.PredictedScore = MathF.Max(0f, MathF.Min(100f, MathF.Round(prediction.PredictedScore, 1)));

            return Ok(prediction);
        }
    }
}
