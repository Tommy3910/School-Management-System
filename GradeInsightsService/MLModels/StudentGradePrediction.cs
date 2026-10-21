using Microsoft.ML.Data;

namespace GradeInsightsService.MLModels
{
    // Output prediction schema
    public class StudentGradePrediction
    {
        [ColumnName("Score")]
        public float PredictedScore { get; set; }
    }
}
