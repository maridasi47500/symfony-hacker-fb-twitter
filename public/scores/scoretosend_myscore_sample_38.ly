
%% Fichier LilyPond généré par Denemo version 2.5.0

%%http://www.gnu.org/software/denemo/

\version "2.20.0"
#(set-default-paper-size "a6")


CompactChordSymbols = {}
#(define DenemoTransposeStep 0)
#(define DenemoTransposeAccidental 0)
DenemoGlobalTranspose = \void {}
titledPiece = {}
AutoBarline = {}
AutoEndMovementBarline = \bar "|."

% The music follows

MvmntIVoiceI = {
  a4 b c d e f r4 r4

}
%Default Score Layout
\header{DenemoLayoutName = "Default Score Layout"
        instrumentation = \markup { \with-url #'"scheme:(d-BookInstrumentation)" "Partition entière"}
        }

\header {
tagline = \markup {"" on \simple #(strftime "%x" (localtime (current-time)))}

        }



#(set-global-staff-size 42)
\paper {

       }

\score { %Start of Movement
          <<

%Start of Staff
\new Staff = "Part 1"  << 
 \new Voice = "MvmntIVoiceI"  { 
  \clef treble    \key c\major    \time 4/4   \MvmntIVoiceI
                        } %End of voice

                        >> %End of Staff

          >>

       } %End of Movement


