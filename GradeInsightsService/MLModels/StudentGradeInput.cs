using Microsoft.ML.Data;

namespace GradeInsightsService.MLModels
{
    // Input data schema fed to ML.NET
    public class StudentGradeInput
    {
        [LoadColumn(0)]
        public float StudyHours { get; set; }

        [LoadColumn(1)]
        public float AttendancePercentage { get; set; }

        [LoadColumn(2)]
        public float PreviousGrade { get; set; }

        // Optional target column for training/retraining
        [LoadColumn(3), ColumnName("Score")]
        public float Score { get; set; }
    }
}
